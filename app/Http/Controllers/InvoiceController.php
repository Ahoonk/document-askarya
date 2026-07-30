<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Invoice;
use App\Models\Penawaran;
use App\Models\BeritaAcara;
use App\Services\DocumentNumberService;
use App\Services\DocumentSnapshotService;
use App\Services\InvoicePdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    use ResolvesCompanyId;

    private function companyInvoices(int $companyId)
    {
        return Invoice::whereHas('penawaran', fn ($query) => $query->where('company_id', $companyId));
    }

    private function loadInvoiceRelations(Invoice $invoice): Invoice
    {
        return $invoice->load([
            'penawaran.company',
            'penawaran.mitra',
            'penawaran.items',
            'purchasingOrder',
            'suratJalan',
            'beritaAcara',
            'fakturPajak',
        ]);
    }

    private function isAlderaInvoiceFormat(?string $nomor): bool
    {
        return is_string($nomor) && preg_match('/^INV\/\d{4}\/(?:\d{2}|[IVXLCDM]+)\/\d{3}-ASK$/', $nomor) === 1;
    }

    private function isNewAlderaInvoiceFormat(?string $nomor): bool
    {
        return is_string($nomor) && preg_match('/^INV\/\d{4}\/[IVXLCDM]+\/\d{3}-ASK$/', $nomor) === 1;
    }

    private function serializeInvoice(Invoice $invoice): array
    {
        $penawaran = $invoice->penawaran;

        return [
            'id' => $invoice->id,
            'nomor' => $invoice->nomor,
            'tanggal' => optional($invoice->tanggal)->format('Y-m-d'),
            'sequence' => $invoice->sequence,
            'total' => (float) $invoice->total,
            'payment_status' => $invoice->payment_status,
            'payment_date' => optional($invoice->payment_date)->format('Y-m-d'),
            'penawaran' => $penawaran ? [
                'id' => $penawaran->id,
                'nomor' => $penawaran->nomor,
                'to_company' => $penawaran->to_company,
                'jenis_kontrak' => $penawaran->jenis_kontrak,
            ] : null,
            'purchasing_order' => $invoice->purchasingOrder ? [
                'id' => $invoice->purchasingOrder->id,
                'nomor_po' => $invoice->purchasingOrder->nomor_po,
                'tanggal_po' => optional($invoice->purchasingOrder->tanggal_po)->format('Y-m-d'),
                'dokumen_name' => $invoice->purchasingOrder->dokumen_name,
            ] : null,
            'surat_jalan' => $invoice->suratJalan ? [
                'id' => $invoice->suratJalan->id,
                'nomor' => $invoice->suratJalan->nomor,
                'tanggal' => optional($invoice->suratJalan->tanggal)->format('Y-m-d'),
            ] : null,
            'berita_acara' => $invoice->beritaAcara ? [
                'id' => $invoice->beritaAcara->id,
                'nomor' => $invoice->beritaAcara->nomor,
                'tanggal' => optional($invoice->beritaAcara->tanggal)->format('Y-m-d'),
            ] : null,
            'faktur_pajak' => $invoice->fakturPajak ? [
                'id' => $invoice->fakturPajak->id,
                'dokumen_name' => $invoice->fakturPajak->dokumen_name,
                'payment_status' => $invoice->fakturPajak->payment_status,
                'payment_date' => optional($invoice->fakturPajak->payment_date)->format('Y-m-d'),
            ] : null,
        ];
    }

    public function index(): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $invoices = $this->companyInvoices($companyId)
            ->with(['penawaran.mitra', 'purchasingOrder', 'suratJalan', 'beritaAcara', 'fakturPajak'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Invoice $invoice) => $this->serializeInvoice($this->loadInvoiceRelations($invoice)))
            ->values();

        return Inertia::render('Invoice/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function show(Invoice $invoice): Response
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $this->loadInvoiceRelations($invoice);
        abort_if(! $invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);

        return Inertia::render('Invoice/Show', [
            'invoice' => $this->serializeInvoice($invoice),
        ]);
    }

    public function previewPdf(Invoice $invoice)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $this->loadInvoiceRelations($invoice);
        abort_if(! $invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);

        $pdf = app(InvoicePdfService::class)->renderPreview($invoice);
        $disposition = request()->boolean('download') ? 'attachment' : 'inline';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="invoice-preview.pdf"',
        ]);
    }

    public function updatePrintDate(Request $request, Invoice $invoice): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $invoice->load('penawaran.company', 'penawaran.mitra', 'penawaran.items', 'purchasingOrder', 'suratJalan', 'beritaAcara');
        abort_if(! $invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
        ]);

        return DB::transaction(function () use ($invoice, $companyId, $validated) {
            $currentYearMonth = optional($invoice->tanggal)?->format('Y-m');
            $nextYearMonth = \Illuminate\Support\Carbon::parse($validated['tanggal'])->format('Y-m');
            $shouldRenumber = $this->isAlderaInvoiceFormat($invoice->nomor)
                && (! $this->isNewAlderaInvoiceFormat($invoice->nomor) || $currentYearMonth !== $nextYearMonth);

            $numberService = app(DocumentNumberService::class);
            $invoiceNumber = $shouldRenumber
                ? $numberService->nextAlderaInvoice($companyId, $validated['tanggal'], $invoice->id)
                : $invoice->nomor;

            $invoice->update([
                'tanggal' => $validated['tanggal'],
                'nomor' => $invoiceNumber,
            ]);

            $invoice->penawaran?->update([
                'invoice_date' => $validated['tanggal'],
                'invoice_number' => $invoiceNumber,
                'invoice_sequence' => $invoice->sequence,
            ]);

            if ($invoice->suratJalan) {
                $suratJalanNumber = $shouldRenumber
                    ? $numberService->alderaNumberFromInvoice($invoiceNumber, 'SJ') ?? $invoice->suratJalan->nomor
                    : $invoice->suratJalan->nomor;

                $invoice->suratJalan->update([
                    'tanggal' => $validated['tanggal'],
                    'nomor' => $suratJalanNumber,
                ]);
            }

            if ($invoice->beritaAcara) {
                $beritaAcaraNumber = $shouldRenumber
                    ? $numberService->alderaNumberFromInvoice($invoiceNumber, 'BA') ?? $invoice->beritaAcara->nomor
                    : $invoice->beritaAcara->nomor;

                $invoice->beritaAcara->update([
                    'tanggal' => $validated['tanggal'],
                    'nomor' => $beritaAcaraNumber,
                    'kota_tanggal_manual' => $validated['tanggal'],
                ]);
            }

            app(DocumentSnapshotService::class)->refreshPenawaranAndRelatedDocuments(
                $invoice->penawaran->refresh()->load(['company', 'mitra', 'items', 'user', 'invoices.purchasingOrder', 'invoices.suratJalan', 'invoices.beritaAcara'])
            );

            return back()->with('success', 'Tanggal invoice berhasil diperbarui dan dokumen turunannya ikut disinkronkan.');
        });
    }

    public function verifyPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $companyId = $this->getCompanyIdOrRedirect();
        $invoice->load('penawaran');
        abort_if(! $invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
        ]);

        $invoice->update([
            'payment_status' => 'paid',
            'payment_date' => $validated['payment_date'],
        ]);

        return back()->with('success', 'Status pembayaran invoice berhasil diubah menjadi sudah dibayarkan.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();
        $invoice->load('penawaran.purchasingOrder', 'purchasingOrder', 'suratJalan', 'beritaAcara', 'fakturPajak');
        abort_if(! $invoice->penawaran || $invoice->penawaran->company_id !== $companyId, 403);

        DB::transaction(function () use ($invoice) {
            $penawaran = $invoice->penawaran;

            if ($invoice->fakturPajak) {
                $path = $invoice->fakturPajak->dokumen_path;
                $invoice->fakturPajak->delete();
                if ($path) {
                    Storage::disk('public')->delete($path);
                }
            }

            if ($invoice->suratJalan) {
                $invoice->suratJalan->delete();
            }

            if ($invoice->beritaAcara) {
                $invoice->beritaAcara->delete();
            }

            if ($invoice->purchasingOrder) {
                $poPath = $invoice->purchasingOrder->dokumen_path;
                $invoice->purchasingOrder->delete();
                if ($poPath) {
                    Storage::disk('public')->delete($poPath);
                }
            }

            $penawaran->update([
                'status' => 'draft',
                'approved_by' => null,
                'approved_at' => null,
                'invoice_date' => null,
                'invoice_number' => null,
                'invoice_sequence' => null,
            ]);

            $invoice->delete();

            app(DocumentSnapshotService::class)->refreshPenawaranAndRelatedDocuments($penawaran);
        });

        return redirect()
            ->route('penawaran.show', $invoice->penawaran)
            ->with('success', 'Invoice berhasil dihapus dan penawaran dikembalikan ke draft.');
    }
}
