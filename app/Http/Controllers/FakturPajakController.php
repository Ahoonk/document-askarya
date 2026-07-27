<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\FakturPajak;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;

class FakturPajakController extends Controller
{
    use ResolvesCompanyId;

    private function companyInvoices(int $companyId): Builder
    {
        return Invoice::query()->whereHas('penawaran', fn ($query) => $query->where('company_id', $companyId));
    }

    private function companyFakturPajaks(int $companyId): Builder
    {
        return FakturPajak::query()->where('company_id', $companyId);
    }

    private function loadRelations(FakturPajak $fakturPajak): FakturPajak
    {
        return $fakturPajak->load([
            'invoice.penawaran.company',
            'invoice.penawaran.mitra',
            'invoice.penawaran.items',
            'invoice.purchasingOrder',
            'invoice.suratJalan',
            'invoice.beritaAcara',
        ]);
    }

    private function serializeInvoice(Invoice $invoice): array
    {
        $penawaran = $invoice->penawaran;

        return [
            'id' => $invoice->id,
            'nomor' => $invoice->nomor,
            'tanggal' => optional($invoice->tanggal)->format('Y-m-d'),
            'customer_name' => $penawaran?->to_company ?? $penawaran?->customer_nama,
            'customer_address' => $penawaran?->to_address,
            'total' => (float) $penawaran?->total,
            'payment_status' => $invoice->payment_status,
        ];
    }

    private function serializeFakturPajak(FakturPajak $fakturPajak): array
    {
        $invoice = $fakturPajak->invoice;
        $penawaran = $invoice?->penawaran;

        return [
            'id' => $fakturPajak->id,
            'company_id' => $fakturPajak->company_id,
            'invoice_id' => $fakturPajak->invoice_id,
            'dokumen_path' => $fakturPajak->dokumen_path,
            'dokumen_name' => $fakturPajak->dokumen_name,
            'uploaded_by' => $fakturPajak->uploaded_by,
            'uploaded_at' => optional($fakturPajak->uploaded_at)->toISOString(),
            'payment_status' => $fakturPajak->payment_status,
            'payment_date' => optional($fakturPajak->payment_date)->format('Y-m-d'),
            'created_at' => optional($fakturPajak->created_at)->toISOString(),
            'updated_at' => optional($fakturPajak->updated_at)->toISOString(),
            'preview_url' => route('faktur-pajak.preview', $fakturPajak->id),
            'invoice' => $invoice ? [
                'id' => $invoice->id,
                'nomor' => $invoice->nomor,
                'tanggal' => optional($invoice->tanggal)->format('Y-m-d'),
                'customer_name' => $penawaran?->to_company ?? $penawaran?->customer_nama,
                'customer_address' => $penawaran?->to_address,
                'total' => (float) $penawaran?->total,
                'penawaran_nomor' => $penawaran?->nomor,
            ] : null,
        ];
    }

    private function uploadDocument(Request $request, ?string $existingPath = null): ?string
    {
        if ($request->hasFile('dokumen')) {
            $file = $request->file('dokumen');

            return $file->store('faktur-pajaks', 'public');
        }

        return $existingPath;
    }

    private function deleteDocument(?string $path): void
    {
        if ($path && str_starts_with($path, 'faktur-pajaks/')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function validationRules(bool $isUpdate = false): array
    {
        return [
            'invoice_id' => $isUpdate ? ['sometimes', 'integer'] : ['required', 'exists:invoices,id', 'unique:faktur_pajaks,invoice_id'],
            'dokumen' => $isUpdate ? ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'] : ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'payment_status' => ['required', Rule::in(['unpaid', 'paid'])],
            'payment_date' => ['nullable', 'date', 'required_if:payment_status,paid'],
        ];
    }

    public function index(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $query = $this->companyFakturPajaks($companyId);

        $fakturPajaks = (clone $query)
            ->with(['invoice.penawaran', 'invoice.purchasingOrder'])
            ->orderByDesc('uploaded_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (FakturPajak $fakturPajak) => $this->serializeFakturPajak($this->loadRelations($fakturPajak)))
            ->values();

        return Inertia::render('FakturPajak/Index', [
            'fakturPajaks' => $fakturPajaks,
            'stats' => [
                'total' => (clone $query)->count(),
                'unpaid' => (clone $query)->where('payment_status', 'unpaid')->count(),
                'paid' => (clone $query)->where('payment_status', 'paid')->count(),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $selectedInvoiceId = $request->integer('invoice_id');

        $availableInvoices = $this->companyInvoices($companyId)
            ->whereDoesntHave('fakturPajak')
            ->with(['penawaran', 'purchasingOrder'])
            ->latest('tanggal')
            ->latest('id')
            ->get()
            ->map(fn (Invoice $invoice) => $this->serializeInvoice($invoice))
            ->values();

        return Inertia::render('FakturPajak/Create', [
            'availableInvoices' => $availableInvoices,
            'selectedInvoiceId' => $selectedInvoiceId ?: null,
            'meta' => [
                'defaults' => [
                    'payment_status' => 'unpaid',
                    'payment_date' => now()->format('Y-m-d'),
                ],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $validated = $request->validate($this->validationRules());

        $invoice = $this->companyInvoices($companyId)
            ->whereKey($validated['invoice_id'])
            ->whereDoesntHave('fakturPajak')
            ->firstOrFail();

        return DB::transaction(function () use ($request, $validated, $invoice, $companyId) {
            $dokumenPath = $this->uploadDocument($request);

            $fakturPajak = FakturPajak::create([
                'company_id' => $companyId,
                'invoice_id' => $invoice->id,
                'dokumen_path' => $dokumenPath,
                'dokumen_name' => $request->file('dokumen')->getClientOriginalName(),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
                'payment_status' => $validated['payment_status'],
                'payment_date' => $validated['payment_date'] ?? null,
            ]);

            return redirect()->route('faktur-pajak.show', $fakturPajak)->with('success', 'Faktur Pajak berhasil diupload.');
        });
    }

    public function preview(FakturPajak $fakturPajak)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($fakturPajak->company_id !== $companyId, 403);

        $disk = Storage::disk('public');
        abort_unless($fakturPajak->dokumen_path && $disk->exists($fakturPajak->dokumen_path), 404);

        $absolutePath = $disk->path($fakturPajak->dokumen_path);
        $mimeType = $disk->mimeType($fakturPajak->dokumen_path) ?: 'application/octet-stream';
        $disposition = str_starts_with($mimeType, 'image/') || $mimeType === 'application/pdf'
            ? 'inline'
            : 'attachment';
        $fileName = $fakturPajak->dokumen_name ?: basename($fakturPajak->dokumen_path);

        return response()->file($absolutePath, [
            'Content-Disposition' => $disposition . '; filename="' . addslashes($fileName) . '"',
            'Content-Type' => $mimeType,
        ]);
    }

    public function show(FakturPajak $fakturPajak): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($fakturPajak->company_id !== $companyId, 403);

        $this->loadRelations($fakturPajak);

        return Inertia::render('FakturPajak/Show', [
            'fakturPajak' => $this->serializeFakturPajak($fakturPajak),
        ]);
    }

    public function edit(FakturPajak $fakturPajak): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($fakturPajak->company_id !== $companyId, 403);

        $availableInvoices = $this->companyInvoices($companyId)
            ->where(function ($query) use ($fakturPajak) {
                $query->whereDoesntHave('fakturPajak')
                    ->orWhereKey($fakturPajak->invoice_id);
            })
            ->with(['penawaran', 'purchasingOrder'])
            ->latest('tanggal')
            ->latest('id')
            ->get()
            ->map(fn (Invoice $invoice) => $this->serializeInvoice($invoice))
            ->values();

        $this->loadRelations($fakturPajak);

        return Inertia::render('FakturPajak/Edit', [
            'fakturPajak' => $this->serializeFakturPajak($fakturPajak),
            'availableInvoices' => $availableInvoices,
            'meta' => [
                'defaults' => [
                    'payment_status' => $fakturPajak->payment_status,
                    'payment_date' => optional($fakturPajak->payment_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
                ],
            ],
        ]);
    }

    public function update(Request $request, FakturPajak $fakturPajak): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($fakturPajak->company_id !== $companyId, 403);

        $validated = $request->validate($this->validationRules(true));
        $oldPath = $fakturPajak->dokumen_path;
        $newPath = $this->uploadDocument($request, $oldPath);

        DB::transaction(function () use ($request, $validated, $fakturPajak, $newPath, $oldPath) {
            $fakturPajak->update([
                'dokumen_path' => $newPath,
                'dokumen_name' => $request->hasFile('dokumen') ? $request->file('dokumen')->getClientOriginalName() : $fakturPajak->dokumen_name,
                'payment_status' => $validated['payment_status'],
                'payment_date' => $validated['payment_status'] === 'paid'
                    ? ($validated['payment_date'] ?? now()->toDateString())
                    : ($validated['payment_date'] ?? null),
            ]);

            if ($request->hasFile('dokumen') && $oldPath !== $newPath) {
                $this->deleteDocument($oldPath);
            }
        });

        return redirect()->route('faktur-pajak.show', $fakturPajak)->with('success', 'Faktur Pajak berhasil diperbarui.');
    }

    public function destroy(FakturPajak $fakturPajak): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($fakturPajak->company_id !== $companyId, 403);

        $path = $fakturPajak->dokumen_path;
        $fakturPajak->delete();
        $this->deleteDocument($path);

        return redirect()->route('faktur-pajak.index')->with('success', 'Faktur Pajak berhasil dihapus.');
    }
}
