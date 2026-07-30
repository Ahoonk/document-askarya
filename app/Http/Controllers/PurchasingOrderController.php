<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Invoice;
use App\Models\BeritaAcara;
use App\Models\Penawaran;
use App\Models\PurchasingOrder;
use App\Models\SuratJalan;
use App\Services\DocumentNumberService;
use App\Services\DocumentSnapshotService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class PurchasingOrderController extends Controller
{
    use ResolvesCompanyId;

    private function companyPenawarans(int $companyId): Builder
    {
        return Penawaran::query()->where('company_id', $companyId);
    }

    private function loadPenawaranSummary(Penawaran $penawaran): Penawaran
    {
        return $penawaran->load([
            'mitra:id,nama,nomor_penawaran,nomor_invoice,nomor_surat_jalan,nomor_berita_acara,template_penawaran_path,template_invoice_path,template_surat_jalan_path,template_berita_acara_path',
            'items',
            'purchasingOrder',
            'invoices' => fn ($query) => $query->orderByDesc('tanggal')->orderByDesc('id'),
        ]);
    }

    private function serializePenawaran(Penawaran $penawaran): array
    {
        $latestInvoice = $penawaran->invoices->first();

        return [
            'id' => $penawaran->id,
            'nomor' => $penawaran->nomor,
            'tanggal' => optional($penawaran->tanggal)->format('Y-m-d'),
            'to_company' => $penawaran->to_company,
            'to_address' => $penawaran->to_address,
            'jenis_kontrak' => $penawaran->jenis_kontrak,
            'status' => $penawaran->status,
            'total' => (float) $penawaran->total,
            'mitra' => $penawaran->mitra ? [
                'id' => $penawaran->mitra->id,
                'nama' => $penawaran->mitra->nama,
                'nomor_penawaran' => $penawaran->mitra->nomor_penawaran,
                'nomor_invoice' => $penawaran->mitra->nomor_invoice,
                'nomor_surat_jalan' => $penawaran->mitra->nomor_surat_jalan,
                'nomor_berita_acara' => $penawaran->mitra->nomor_berita_acara,
                'template_penawaran_path' => $penawaran->mitra->template_penawaran_path,
                'template_invoice_path' => $penawaran->mitra->template_invoice_path,
                'template_surat_jalan_path' => $penawaran->mitra->template_surat_jalan_path,
                'template_berita_acara_path' => $penawaran->mitra->template_berita_acara_path,
            ] : null,
            'items' => $penawaran->items->map(fn ($item) => [
                'id' => $item->id,
                'nama' => $item->nama,
                'rincian' => $item->rincian,
                'qty' => (float) $item->qty,
                'satuan' => $item->satuan,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ])->values()->all(),
            'purchasing_order' => $penawaran->purchasingOrder ? [
                'id' => $penawaran->purchasingOrder->id,
                'dokumen_path' => $penawaran->purchasingOrder->dokumen_path,
                'dokumen_name' => $penawaran->purchasingOrder->dokumen_name,
                'preview_url' => route('purchasing-order.preview', $penawaran->purchasingOrder->id),
                'nomor_po' => $penawaran->purchasingOrder->nomor_po,
                'tanggal_po' => optional($penawaran->purchasingOrder->tanggal_po)->format('Y-m-d'),
            ] : null,
            'latest_invoice' => $latestInvoice ? [
                'id' => $latestInvoice->id,
                'nomor' => $latestInvoice->nomor,
                'tanggal' => optional($latestInvoice->tanggal)->format('Y-m-d'),
                'sequence' => $latestInvoice->sequence,
                'payment_status' => $latestInvoice->payment_status,
            ] : null,
        ];
    }

    private function createOrRefreshBeritaAcara(Invoice $invoice, int $companyId, string $invoiceDate): BeritaAcara
    {
        $invoice->loadMissing(['penawaran.company', 'penawaran.mitra', 'penawaran.items', 'purchasingOrder', 'beritaAcara']);

        $mitra = $invoice->penawaran?->mitra;
        $numberService = app(DocumentNumberService::class);

        if ($mitra && ! empty($mitra->nomor_berita_acara)) {
            $nomor = $mitra->nomor_berita_acara;
        } else {
            $nomor = $numberService->alderaNumberFromInvoice($invoice->nomor, 'BA')
                ?? $numberService->next($companyId, 'berita_acara', $invoiceDate);
        }

        $beritaAcara = BeritaAcara::firstOrCreate(
            ['invoice_id' => $invoice->id],
            [
                'company_id' => $companyId,
                'nomor' => $nomor,
                'tanggal' => $invoiceDate,
                'perihal' => 'Berita Acara',
                'keterangan_akhir' => 'Demikian berita acara ini dibuat dan dapat digunakan sebagaimana mestinya.',
                'tanggal_teks_manual' => null,
                'kota_tanggal_manual' => $invoiceDate,
                'created_by' => auth()->id(),
            ]
        );

        $beritaAcara->update([
            'snapshot_data' => app(DocumentSnapshotService::class)->forBeritaAcara(
                $beritaAcara->load(['invoice.penawaran.items', 'invoice.purchasingOrder'])
            ),
        ]);

        return $beritaAcara;
    }

    public function index(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $approvedSatuan = $this->companyPenawarans($companyId)
            ->where('status', 'approved')
            ->where('jenis_kontrak', 'satuan')
            ->whereDoesntHave('purchasingOrder')
            ->with(['mitra:id,nama,nomor_penawaran,nomor_invoice,nomor_surat_jalan,nomor_berita_acara,template_penawaran_path,template_invoice_path,template_surat_jalan_path,template_berita_acara_path', 'items'])
            ->latest('tanggal')
            ->latest('id')
            ->get()
            ->map(fn (Penawaran $penawaran) => $this->serializePenawaran($this->loadPenawaranSummary($penawaran)))
            ->values();

        $approvedKontrak = $this->companyPenawarans($companyId)
            ->where('status', 'approved')
            ->where('jenis_kontrak', 'kontrak')
            ->whereDoesntHave('purchasingOrder')
            ->with(['mitra:id,nama,nomor_penawaran,nomor_invoice,nomor_surat_jalan,nomor_berita_acara,template_penawaran_path,template_invoice_path,template_surat_jalan_path,template_berita_acara_path', 'items'])
            ->latest('tanggal')
            ->latest('id')
            ->get()
            ->map(fn (Penawaran $penawaran) => $this->serializePenawaran($this->loadPenawaranSummary($penawaran)))
            ->values();

        $existingData = $this->companyPenawarans($companyId)
            ->where('status', 'approved')
            ->whereHas('purchasingOrder')
            ->with(['mitra:id,nama,nomor_penawaran,nomor_invoice,nomor_surat_jalan,nomor_berita_acara,template_penawaran_path,template_invoice_path,template_surat_jalan_path,template_berita_acara_path', 'items', 'purchasingOrder', 'invoices' => fn ($query) => $query->orderByDesc('tanggal')->orderByDesc('id')])
            ->latest('tanggal')
            ->latest('id')
            ->get()
            ->map(fn (Penawaran $penawaran) => $this->serializePenawaran($this->loadPenawaranSummary($penawaran)))
            ->values();

        return Inertia::render('PurchasingOrder/Index', [
            'approvedSatuan' => $approvedSatuan,
            'approvedKontrak' => $approvedKontrak,
            'existingData' => $existingData,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $validated = $request->validate(
            [
                'penawaran_id' => ['required', 'exists:penawarans,id'],
                'nomor_po' => ['required', 'string', 'max:100'],
                'tanggal_po' => ['required', 'date'],
                'dokumen' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            ],
            [
                'dokumen.required' => 'File dokumen wajib diunggah.',
                'dokumen.file' => 'Dokumen harus berupa file yang valid.',
                'dokumen.mimes' => 'Format dokumen harus PDF, JPG, JPEG, atau PNG.',
                'dokumen.max' => 'Ukuran dokumen maksimal 5 MB.',
                'dokumen.uploaded' => 'Upload gagal di server. Cek upload_max_filesize, post_max_size, dan permission folder storage di VPS.',
            ]
        );

        $penawaran = $this->companyPenawarans($companyId)
            ->where('status', 'approved')
            ->where('id', $validated['penawaran_id'])
            ->firstOrFail();

        if ($penawaran->purchasingOrder) {
            return back()->with('status', 'Dokumen Purchasing Order untuk penawaran ini sudah pernah diupload.');
        }

        $file = $request->file('dokumen');
        $path = $file->store('purchasing-orders', 'public');

        PurchasingOrder::create([
            'company_id' => $companyId,
            'penawaran_id' => $penawaran->id,
            'dokumen_path' => $path,
            'dokumen_name' => $file->getClientOriginalName(),
            'nomor_po' => $validated['nomor_po'],
            'tanggal_po' => $validated['tanggal_po'],
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
        ]);

        return redirect()->route('purchasing-order.index')->with('success', 'Dokumen PO berhasil diupload.');
    }

    public function preview(PurchasingOrder $purchasingOrder)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $purchasingOrder->load('penawaran');
        abort_if(! $purchasingOrder->penawaran || $purchasingOrder->penawaran->company_id !== $companyId, 403);

        $disk = Storage::disk('public');
        abort_unless($purchasingOrder->dokumen_path && $disk->exists($purchasingOrder->dokumen_path), 404);

        $absolutePath = $disk->path($purchasingOrder->dokumen_path);
        $mimeType = $disk->mimeType($purchasingOrder->dokumen_path) ?: 'application/octet-stream';
        $disposition = str_starts_with($mimeType, 'image/') || $mimeType === 'application/pdf'
            ? 'inline'
            : 'attachment';
        $fileName = $purchasingOrder->dokumen_name ?: basename($purchasingOrder->dokumen_path);

        return response()->file($absolutePath, [
            'Content-Disposition' => $disposition . '; filename="' . addslashes($fileName) . '"',
            'Content-Type' => $mimeType,
        ]);
    }

    public function createInvoice(Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);
        abort_if($penawaran->status !== 'approved', 403);
        abort_if(! $penawaran->purchasingOrder, 403);
        abort_if($penawaran->invoices()->exists(), 403);

        return DB::transaction(function () use ($penawaran, $companyId) {
            $sequence = 1;
            $invoiceDate = now()->toDateString();
            $mitra = $penawaran->mitra;
            $numberService = app(DocumentNumberService::class);

            if ($mitra) {
                if (empty($mitra->nomor_invoice)) {
                    throw ValidationException::withMessages([
                        'penawaran_id' => 'Nomor invoice mitra harus diisi manual di master Mitra.',
                    ]);
                }

                $invoiceNumber = $mitra->nomor_invoice;
            } else {
                $invoiceNumber = $numberService->nextAlderaInvoice($companyId, $invoiceDate);
            }

            $invoice = Invoice::create([
                'company_id' => $companyId,
                'penawaran_id' => $penawaran->id,
                'purchasing_order_id' => $penawaran->purchasingOrder->id,
                'nomor' => $invoiceNumber,
                'tanggal' => $invoiceDate,
                'sequence' => $sequence,
                'total' => $penawaran->total,
                'created_by' => auth()->id(),
            ]);

            $invoice->update([
                'snapshot_data' => app(DocumentSnapshotService::class)->forInvoice($invoice),
            ]);

            if ($mitra) {
                if (empty($mitra->nomor_surat_jalan)) {
                    throw ValidationException::withMessages([
                        'penawaran_id' => 'Nomor surat jalan mitra harus diisi manual di master Mitra.',
                    ]);
                }

                $suratJalanNomor = $mitra->nomor_surat_jalan;
            } else {
                $suratJalanNomor = $numberService->alderaNumberFromInvoice($invoiceNumber, 'SJ')
                    ?? $numberService->next($companyId, 'surat_jalan', $invoiceDate);
            }

            $suratJalan = SuratJalan::firstOrCreate(
                ['invoice_id' => $invoice->id],
                [
                    'company_id' => $companyId,
                    'nomor' => $suratJalanNomor,
                    'tanggal' => $invoiceDate,
                    'created_by' => auth()->id(),
                ]
            );

            $suratJalan->update([
                'snapshot_data' => app(DocumentSnapshotService::class)->forSuratJalan($suratJalan),
            ]);

            $this->createOrRefreshBeritaAcara($invoice, $companyId, $invoiceDate);

            $penawaran->update([
                'invoice_date' => $invoiceDate,
                'invoice_sequence' => $sequence,
                'invoice_number' => $invoiceNumber,
            ]);

            app(DocumentSnapshotService::class)->refreshPenawaranAndRelatedDocuments($penawaran);

            return redirect()->route('invoice.show', $invoice)->with('success', 'Invoice berhasil dibuat dari PO.');
        });
    }

    public function nextInvoice(Request $request, Penawaran $penawaran)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);
        abort_if($penawaran->status !== 'approved', 403);
        abort_if($penawaran->jenis_kontrak !== 'kontrak', 403);
        abort_if(! $penawaran->purchasingOrder, 403);

        $validated = $request->validate([
            'invoice_date' => ['required', 'date'],
        ]);

        return DB::transaction(function () use ($penawaran, $companyId, $validated) {
            $latestSequence = (int) $penawaran->invoices()->max('sequence');
            abort_if($latestSequence < 1, 403);

            $currentSequence = max((int) $penawaran->invoice_sequence, $latestSequence, 1);
            $nextSequence = $currentSequence + 1;
            $mitra = $penawaran->mitra;
            $numberService = app(DocumentNumberService::class);

            if ($mitra) {
                if (empty($mitra->nomor_invoice)) {
                    throw ValidationException::withMessages([
                        'penawaran_id' => 'Nomor invoice mitra harus diisi manual di master Mitra.',
                    ]);
                }

                $invoiceNumber = $mitra->nomor_invoice;
            } else {
                $invoiceNumber = $numberService->nextAlderaInvoice($companyId, $validated['invoice_date']);
            }

            $invoice = Invoice::create([
                'company_id' => $companyId,
                'penawaran_id' => $penawaran->id,
                'purchasing_order_id' => $penawaran->purchasingOrder->id,
                'nomor' => $invoiceNumber,
                'tanggal' => $validated['invoice_date'],
                'sequence' => $nextSequence,
                'total' => $penawaran->total,
                'created_by' => auth()->id(),
            ]);

            $invoice->update([
                'snapshot_data' => app(DocumentSnapshotService::class)->forInvoice($invoice),
            ]);

            if ($mitra) {
                if (empty($mitra->nomor_surat_jalan)) {
                    throw ValidationException::withMessages([
                        'penawaran_id' => 'Nomor surat jalan mitra harus diisi manual di master Mitra.',
                    ]);
                }

                $suratJalanNomor = $mitra->nomor_surat_jalan;
            } else {
                $suratJalanNomor = $numberService->alderaNumberFromInvoice($invoiceNumber, 'SJ')
                    ?? $numberService->next($companyId, 'surat_jalan', $validated['invoice_date']);
            }

            $suratJalan = SuratJalan::firstOrCreate(
                ['invoice_id' => $invoice->id],
                [
                    'company_id' => $companyId,
                    'nomor' => $suratJalanNomor,
                    'tanggal' => $validated['invoice_date'],
                    'created_by' => auth()->id(),
                ]
            );

            $suratJalan->update([
                'snapshot_data' => app(DocumentSnapshotService::class)->forSuratJalan($suratJalan),
            ]);

            $this->createOrRefreshBeritaAcara($invoice, $companyId, $validated['invoice_date']);

            $penawaran->update([
                'invoice_sequence' => $nextSequence,
                'invoice_date' => $validated['invoice_date'],
                'invoice_number' => $invoiceNumber,
            ]);

            app(DocumentSnapshotService::class)->refreshPenawaranAndRelatedDocuments($penawaran);

            return redirect()->route('invoice.show', $invoice)->with('success', 'Invoice berikutnya berhasil dibuat.');
        });
    }

    public function cancelApproved(Penawaran $penawaran): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        abort_if($penawaran->company_id !== $companyId, 403);
        abort_if($penawaran->status !== 'approved', 403);
        abort_if($penawaran->purchasingOrder, 403);

        $penawaran->update([
            'status' => 'submitted',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('purchasing-order.index')->with('success', 'Status penawaran dikembalikan ke submitted.');
    }
}
