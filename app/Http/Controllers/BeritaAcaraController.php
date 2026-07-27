<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\BeritaAcara;
use App\Models\Invoice;
use App\Services\DocumentNumberService;
use App\Services\DocumentSnapshotService;
use App\Services\BeritaAcaraPdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class BeritaAcaraController extends Controller
{
    use ResolvesCompanyId;

    private function companyInvoices(int $companyId): Builder
    {
        return Invoice::query()->whereHas('penawaran', fn ($query) => $query->where('company_id', $companyId));
    }

    private function companyBeritaAcaras(int $companyId): Builder
    {
        return BeritaAcara::query()->where('company_id', $companyId);
    }

    private function loadRelations(BeritaAcara $beritaAcara): BeritaAcara
    {
        return $beritaAcara->load([
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
            'po_number' => $invoice->purchasingOrder?->nomor_po,
        ];
    }

    private function serializeBeritaAcara(BeritaAcara $beritaAcara): array
    {
        $invoice = $beritaAcara->invoice;
        $penawaran = $invoice?->penawaran;

        return [
            'id' => $beritaAcara->id,
            'company_id' => $beritaAcara->company_id,
            'invoice_id' => $beritaAcara->invoice_id,
            'nomor' => $beritaAcara->nomor,
            'tanggal' => optional($beritaAcara->tanggal)->format('Y-m-d'),
            'perihal' => $beritaAcara->perihal,
            'nomor_perjanjian' => $beritaAcara->nomor_perjanjian,
            'tanggal_teks_manual' => $beritaAcara->tanggal_teks_manual,
            'pihak_pertama_nama' => $beritaAcara->pihak_pertama_nama,
            'pihak_pertama_alamat' => $beritaAcara->pihak_pertama_alamat,
            'pihak_kedua_nama' => $beritaAcara->pihak_kedua_nama,
            'pihak_kedua_alamat' => $beritaAcara->pihak_kedua_alamat,
            'pekerjaan_manual' => $beritaAcara->pekerjaan_manual,
            'periode_manual' => $beritaAcara->periode_manual,
            'predikat_manual' => $beritaAcara->predikat_manual,
            'keterangan_akhir' => $beritaAcara->keterangan_akhir,
            'kota_tanggal_manual' => optional($beritaAcara->kota_tanggal_manual)->format('Y-m-d'),
            'snapshot_data' => $beritaAcara->snapshot_data,
            'created_at' => optional($beritaAcara->created_at)->toISOString(),
            'updated_at' => optional($beritaAcara->updated_at)->toISOString(),
            'preview_url' => route('berita-acara.preview', [
                'beritaAcara' => $beritaAcara->id,
                'v' => optional($beritaAcara->updated_at)->timestamp ?? now()->timestamp,
            ]),
            'invoice' => $invoice ? [
                'id' => $invoice->id,
                'nomor' => $invoice->nomor,
                'tanggal' => optional($invoice->tanggal)->format('Y-m-d'),
                'customer_name' => $penawaran?->to_company ?? $penawaran?->customer_nama,
                'customer_address' => $penawaran?->to_address,
                'po_number' => $invoice->purchasingOrder?->nomor_po,
                'total' => (float) $penawaran?->total,
            ] : null,
        ];
    }

    private function beritaAcaraDefaults(int $companyId): array
    {
        $company = \App\Models\Company::find($companyId);

        return [
            'tanggal' => now()->format('Y-m-d'),
            'kota_tanggal_manual' => now()->format('Y-m-d'),
            'perihal' => 'Penggunaan Internet',
            'keterangan_akhir' => 'Demikian Berita Acara ini dibuat dan dapat digunakan sebagai mana mestinya.',
            'tanggal_teks_manual' => '',
            'pihak_kedua_nama' => $company?->name ?: 'PT Aldera Saddatech Karya',
            'pihak_kedua_alamat' => $company && $company->address !== '-'
                ? $company->address
                : 'Link. Acing Baru RT 001 RW 007, Kelurahan Masigit, Kecamatan Jombang, Kota Cilegon, Provinsi Banten.',
        ];
    }

    public function index(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $query = $this->companyBeritaAcaras($companyId);

        $beritaAcaras = (clone $query)
            ->with(['invoice.penawaran', 'invoice.purchasingOrder'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->map(fn (BeritaAcara $beritaAcara) => $this->serializeBeritaAcara($this->loadRelations($beritaAcara)))
            ->values();

        return Inertia::render('BeritaAcara/Index', [
            'beritaAcaras' => $beritaAcaras,
            'stats' => [
                'total' => (clone $query)->count(),
                'this_month' => (clone $query)->whereYear('tanggal', now()->year)->whereMonth('tanggal', now()->month)->count(),
                'linked_invoice' => (clone $query)->whereNotNull('invoice_id')->count(),
            ],
        ]);
    }

    public function preview(BeritaAcara $beritaAcara)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($beritaAcara->company_id !== $companyId, 403);

        $pdf = app(BeritaAcaraPdfService::class)->renderPreview($this->loadRelations($beritaAcara));

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="berita-acara-preview.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function create(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $availableInvoices = $this->companyInvoices($companyId)
            ->whereDoesntHave('beritaAcara')
            ->with(['penawaran', 'purchasingOrder'])
            ->latest('tanggal')
            ->latest('id')
            ->get()
            ->map(fn (Invoice $invoice) => $this->serializeInvoice($invoice))
            ->values();

        return Inertia::render('BeritaAcara/Create', [
            'availableInvoices' => $availableInvoices,
            'meta' => [
                'defaults' => $this->beritaAcaraDefaults($companyId),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id', 'unique:berita_acaras,invoice_id'],
            'tanggal' => ['required', 'date'],
            'perihal' => ['nullable', 'string', 'max:255'],
            'nomor_perjanjian' => ['nullable', 'string', 'max:255'],
            'tanggal_teks_manual' => ['nullable', 'string', 'max:255'],
            'pihak_pertama_nama' => ['nullable', 'string', 'max:255'],
            'pihak_pertama_alamat' => ['nullable', 'string'],
            'pihak_kedua_nama' => ['nullable', 'string', 'max:255'],
            'pihak_kedua_alamat' => ['nullable', 'string'],
            'pekerjaan_manual' => ['nullable', 'string', 'max:255'],
            'periode_manual' => ['nullable', 'string', 'max:255'],
            'predikat_manual' => ['nullable', 'string', 'max:255'],
            'keterangan_akhir' => ['nullable', 'string'],
            'kota_tanggal_manual' => ['nullable', 'date'],
        ]);

        $invoice = $this->companyInvoices($companyId)
            ->with(['penawaran.mitra', 'purchasingOrder'])
            ->whereKey($validated['invoice_id'])
            ->firstOrFail();

        abort_if($invoice->beritaAcara()->exists(), 403, 'Berita Acara untuk invoice ini sudah ada.');

        return DB::transaction(function () use ($validated, $invoice, $companyId) {
            $mitra = $invoice->penawaran?->mitra;
            $numberService = app(DocumentNumberService::class);

            if ($mitra) {
                if (empty($mitra->nomor_berita_acara)) {
                    throw ValidationException::withMessages([
                        'invoice_id' => 'Nomor berita acara mitra harus diisi manual di master Mitra.',
                    ]);
                }

                $nomor = $mitra->nomor_berita_acara;
            } else {
                $nomor = $numberService->next($companyId, 'berita_acara', $validated['tanggal']);
            }

            $beritaAcara = BeritaAcara::create([
                'company_id' => $companyId,
                'invoice_id' => $invoice->id,
                'nomor' => $nomor,
                'tanggal' => $validated['tanggal'],
                'perihal' => $validated['perihal'] ?? 'Berita Acara',
                'nomor_perjanjian' => $validated['nomor_perjanjian'] ?? null,
                'tanggal_teks_manual' => $validated['tanggal_teks_manual'] ?? null,
                'pihak_pertama_nama' => $validated['pihak_pertama_nama'] ?? null,
                'pihak_pertama_alamat' => $validated['pihak_pertama_alamat'] ?? null,
                'pihak_kedua_nama' => $validated['pihak_kedua_nama'] ?? null,
                'pihak_kedua_alamat' => $validated['pihak_kedua_alamat'] ?? null,
                'pekerjaan_manual' => $validated['pekerjaan_manual'] ?? null,
                'periode_manual' => $validated['periode_manual'] ?? null,
                'predikat_manual' => $validated['predikat_manual'] ?? null,
                'keterangan_akhir' => $validated['keterangan_akhir'] ?? null,
                'kota_tanggal_manual' => $validated['kota_tanggal_manual'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $beritaAcara->update([
                'snapshot_data' => app(DocumentSnapshotService::class)->forBeritaAcara(
                    $beritaAcara->load(['invoice.penawaran.items', 'invoice.purchasingOrder'])
                ),
            ]);

            return redirect()->route('berita-acara.show', $beritaAcara)->with('success', 'Berita Acara berhasil dibuat.');
        });
    }

    public function show(BeritaAcara $beritaAcara): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($beritaAcara->company_id !== $companyId, 403);

        $this->loadRelations($beritaAcara);

        return Inertia::render('BeritaAcara/Show', [
            'beritaAcara' => $this->serializeBeritaAcara($beritaAcara),
            'snapshot' => $beritaAcara->snapshot_data ?? [],
        ]);
    }

    public function edit(BeritaAcara $beritaAcara): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($beritaAcara->company_id !== $companyId, 403);

        $this->loadRelations($beritaAcara);

        return Inertia::render('BeritaAcara/Edit', [
            'beritaAcara' => $this->serializeBeritaAcara($beritaAcara),
            'meta' => [
                'defaults' => $this->beritaAcaraDefaults($companyId),
            ],
        ]);
    }

    public function update(Request $request, BeritaAcara $beritaAcara): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($beritaAcara->company_id !== $companyId, 403);

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'perihal' => ['nullable', 'string', 'max:255'],
            'nomor_perjanjian' => ['nullable', 'string', 'max:255'],
            'tanggal_teks_manual' => ['nullable', 'string', 'max:255'],
            'pihak_pertama_nama' => ['nullable', 'string', 'max:255'],
            'pihak_pertama_alamat' => ['nullable', 'string'],
            'pihak_kedua_nama' => ['nullable', 'string', 'max:255'],
            'pihak_kedua_alamat' => ['nullable', 'string'],
            'pekerjaan_manual' => ['nullable', 'string', 'max:255'],
            'periode_manual' => ['nullable', 'string', 'max:255'],
            'predikat_manual' => ['nullable', 'string', 'max:255'],
            'keterangan_akhir' => ['nullable', 'string'],
            'kota_tanggal_manual' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($beritaAcara, $validated) {
            $beritaAcara->update([
                'tanggal' => $validated['tanggal'],
                'perihal' => $validated['perihal'] ?? 'Berita Acara',
                'nomor_perjanjian' => $validated['nomor_perjanjian'] ?? null,
                'tanggal_teks_manual' => $validated['tanggal_teks_manual'] ?? null,
                'pihak_pertama_nama' => $validated['pihak_pertama_nama'] ?? null,
                'pihak_pertama_alamat' => $validated['pihak_pertama_alamat'] ?? null,
                'pihak_kedua_nama' => $validated['pihak_kedua_nama'] ?? null,
                'pihak_kedua_alamat' => $validated['pihak_kedua_alamat'] ?? null,
                'pekerjaan_manual' => $validated['pekerjaan_manual'] ?? null,
                'periode_manual' => $validated['periode_manual'] ?? null,
                'predikat_manual' => $validated['predikat_manual'] ?? null,
                'keterangan_akhir' => $validated['keterangan_akhir'] ?? null,
                'kota_tanggal_manual' => $validated['kota_tanggal_manual'] ?? null,
            ]);

            $beritaAcara->update([
                'snapshot_data' => app(DocumentSnapshotService::class)->forBeritaAcara($beritaAcara->load(['invoice.penawaran.items', 'invoice.purchasingOrder'])),
            ]);
        });

        return redirect()->route('berita-acara.show', $beritaAcara)->with('success', 'Berita Acara berhasil diperbarui.');
    }

    public function destroy(BeritaAcara $beritaAcara): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($beritaAcara->company_id !== $companyId, 403);

        $beritaAcara->delete();

        return redirect()->route('berita-acara.index')->with('success', 'Berita Acara berhasil dihapus.');
    }
}
