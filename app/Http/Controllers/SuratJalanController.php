<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Invoice;
use App\Models\SuratJalan;
use App\Services\DocumentNumberService;
use App\Services\DocumentSnapshotService;
use App\Services\SuratJalanPdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class SuratJalanController extends Controller
{
    use ResolvesCompanyId;

    private function companyInvoices(int $companyId): Builder
    {
        return Invoice::query()->whereHas('penawaran', fn ($query) => $query->where('company_id', $companyId));
    }

    private function loadRelations(SuratJalan $suratJalan): SuratJalan
    {
        return $suratJalan->load([
            'invoice.penawaran.company',
            'invoice.penawaran.mitra',
            'invoice.penawaran.items',
            'invoice.purchasingOrder',
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
        ];
    }

    private function serializeSuratJalan(SuratJalan $suratJalan): array
    {
        $invoice = $suratJalan->invoice;
        $penawaran = $invoice?->penawaran;

        return [
            'id' => $suratJalan->id,
            'nomor' => $suratJalan->nomor,
            'tanggal' => optional($suratJalan->tanggal)->format('Y-m-d'),
            'kota_tanggal_manual' => optional($suratJalan->kota_tanggal_manual)->format('Y-m-d'),
            'preview_url' => route('surat-jalan.preview', $suratJalan->id),
            'pemberi_nama' => $suratJalan->pemberi_nama,
            'pemberi_jabatan' => $suratJalan->pemberi_jabatan,
            'pemberi_alamat' => $suratJalan->pemberi_alamat,
            'penerima_nama' => $suratJalan->penerima_nama,
            'penerima_hp' => $suratJalan->penerima_hp,
            'invoice' => $invoice ? [
                'id' => $invoice->id,
                'nomor' => $invoice->nomor,
                'tanggal' => optional($invoice->tanggal)->format('Y-m-d'),
                'customer_name' => $penawaran?->to_company ?? $penawaran?->customer_nama,
                'customer_address' => $penawaran?->to_address,
            ] : null,
        ];
    }

    public function index(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $suratJalans = SuratJalan::query()
            ->where('company_id', $companyId)
            ->with(['invoice.penawaran', 'invoice.purchasingOrder'])
            ->latest('tanggal')
            ->latest('id')
            ->get()
            ->map(fn (SuratJalan $suratJalan) => $this->serializeSuratJalan($this->loadRelations($suratJalan)))
            ->values();

        return Inertia::render('SuratJalan/Index', [
            'suratJalans' => $suratJalans,
        ]);
    }

    public function create(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $availableInvoices = $this->companyInvoices($companyId)
            ->whereDoesntHave('suratJalan')
            ->with(['penawaran', 'purchasingOrder'])
            ->latest('tanggal')
            ->latest('id')
            ->get()
            ->map(fn (Invoice $invoice) => $this->serializeInvoice($invoice))
            ->values();

        return Inertia::render('SuratJalan/Create', [
            'availableInvoices' => $availableInvoices,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id', 'unique:surat_jalans,invoice_id'],
            'tanggal' => ['required', 'date'],
            'pemberi_nama' => ['nullable', 'string', 'max:255'],
            'pemberi_jabatan' => ['nullable', 'string', 'max:255'],
            'pemberi_alamat' => ['nullable', 'string'],
            'penerima_nama' => ['nullable', 'string', 'max:255'],
            'penerima_hp' => ['nullable', 'string', 'max:50'],
            'kota_tanggal_manual' => ['nullable', 'date'],
        ]);

        $invoice = $this->companyInvoices($companyId)
            ->with(['penawaran', 'purchasingOrder'])
            ->whereKey($validated['invoice_id'])
            ->firstOrFail();

        abort_if($invoice->suratJalan()->exists(), 403, 'Surat Jalan untuk invoice ini sudah ada.');

        return DB::transaction(function () use ($validated, $invoice, $companyId) {
            $mitra = $invoice->penawaran?->mitra;
            $numberService = app(DocumentNumberService::class);

            if ($mitra) {
                if (empty($mitra->nomor_surat_jalan)) {
                    throw ValidationException::withMessages([
                        'invoice_id' => 'Nomor surat jalan mitra harus diisi manual di master Mitra.',
                    ]);
                }

                $nomor = $mitra->nomor_surat_jalan;
            } else {
                $nomor = $numberService->alderaNumberFromInvoice($invoice->nomor, 'SJ')
                    ?? $numberService->next($companyId, 'surat_jalan', $validated['tanggal']);
            }

            $suratJalan = SuratJalan::create([
                'company_id' => $companyId,
                'invoice_id' => $invoice->id,
                'nomor' => $nomor,
                'tanggal' => $validated['tanggal'],
                'pemberi_nama' => $validated['pemberi_nama'] ?? null,
                'pemberi_jabatan' => $validated['pemberi_jabatan'] ?? null,
                'pemberi_alamat' => $validated['pemberi_alamat'] ?? null,
                'penerima_nama' => $validated['penerima_nama'] ?? null,
                'penerima_hp' => $validated['penerima_hp'] ?? null,
                'kota_tanggal_manual' => $validated['kota_tanggal_manual'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $suratJalan->update([
                'snapshot_data' => app(DocumentSnapshotService::class)->forSuratJalan(
                    $suratJalan->load(['invoice.penawaran.items', 'invoice.purchasingOrder'])
                ),
            ]);

            return redirect()->route('surat-jalan.show', $suratJalan)->with('success', 'Surat Jalan berhasil dibuat.');
        });
    }

    public function show(SuratJalan $suratJalan): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $this->loadRelations($suratJalan);
        abort_if($suratJalan->company_id !== $companyId, 403);

        return Inertia::render('SuratJalan/Show', [
            'suratJalan' => $this->serializeSuratJalan($suratJalan),
            'snapshot' => $suratJalan->snapshot_data ?? [],
        ]);
    }

    public function preview(SuratJalan $suratJalan)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($suratJalan->company_id !== $companyId, 403);

        $pdf = app(SuratJalanPdfService::class)->renderPreview($this->loadRelations($suratJalan));

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="surat-jalan-preview.pdf"',
        ]);
    }
}
