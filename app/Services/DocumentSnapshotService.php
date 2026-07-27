<?php

namespace App\Services;

use App\Models\BeritaAcara;
use App\Models\Mitra;
use App\Models\Invoice;
use App\Models\NotaToko;
use App\Models\Penawaran;
use App\Models\SuratJalan;

class DocumentSnapshotService
{
    public function __construct(
        private readonly DocumentTemplateResolver $templateResolver,
    ) {
    }

    public function refreshPenawaranAndRelatedDocuments(Penawaran $penawaran): void
    {
        $penawaran->load(['company', 'mitra', 'items', 'user', 'invoices.purchasingOrder', 'invoices.suratJalan', 'invoices.beritaAcara']);

        $penawaran->update([
            'snapshot_data' => $this->forPenawaran($penawaran),
        ]);

        foreach ($penawaran->invoices as $invoice) {
            $invoice->setRelation('penawaran', $penawaran);
            $invoice->update([
                'total' => $penawaran->total,
                'snapshot_data' => $this->forInvoice($invoice),
            ]);

            if ($invoice->suratJalan) {
                $invoice->suratJalan->setRelation('invoice', $invoice);
                $invoice->suratJalan->update([
                    'snapshot_data' => $this->forSuratJalan($invoice->suratJalan),
                ]);
            }

            if ($invoice->beritaAcara) {
                $invoice->beritaAcara->setRelation('invoice', $invoice);
                $invoice->beritaAcara->update([
                    'snapshot_data' => $this->forBeritaAcara($invoice->beritaAcara),
                ]);
            }
        }
    }

    public function forPenawaran(Penawaran $penawaran): array
    {
        $penawaran->loadMissing(['company', 'mitra', 'items', 'user']);

        return [
            'company' => [
                'id' => $penawaran->company?->id,
                'name' => $penawaran->company?->name,
                'address' => $penawaran->company?->address,
                'logo' => $penawaran->company?->logo,
            ],
            'mitra' => [
                'id' => $penawaran->mitra?->id,
                'name' => $penawaran->mitra?->nama,
            ],
            'nomor' => $penawaran->nomor,
            'tanggal' => $penawaran->tanggal,
            'customer_name' => $penawaran->to_company ?? $penawaran->customer_nama,
            'customer_address' => $penawaran->to_address,
            'jenis_kontrak' => $penawaran->jenis_kontrak,
            'signature_role' => $penawaran->signature_role,
            'keterangan' => $penawaran->keterangan,
            'template' => $this->resolveTemplateMeta($penawaran->company_id, 'penawaran', $penawaran->mitra),
            'subtotal' => (float) $penawaran->subtotal,
            'tax_percent' => (float) $penawaran->tax_percent,
            'tax_amount' => (float) $penawaran->tax_amount,
            'total' => (float) $penawaran->total,
            'status' => $penawaran->status,
            'invoice_date' => $penawaran->invoice_date,
            'invoice_number' => $penawaran->invoice_number,
            'invoice_sequence' => $penawaran->invoice_sequence,
            'approved_by' => $penawaran->approved_by,
            'approved_at' => $penawaran->approved_at,
            'creator_name' => $penawaran->user?->name,
            'items' => $penawaran->items->map(fn ($item) => [
                'nama' => $item->nama,
                'rincian' => $item->rincian,
                'qty' => (float) $item->qty,
                'satuan' => $item->satuan,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ])->values()->all(),
        ];
    }

    public function forInvoice(Invoice $invoice): array
    {
        $invoice->loadMissing(['penawaran.company', 'penawaran.mitra', 'penawaran.items', 'purchasingOrder', 'creator']);
        $penawaran = $invoice->penawaran;
        $mitra = $penawaran?->mitra;

        return [
            'company' => [
                'id' => $penawaran?->company?->id,
                'name' => $penawaran?->company?->name,
                'address' => $penawaran?->company?->address,
                'logo' => $penawaran?->company?->logo,
            ],
            'is_mitra' => (bool) $mitra,
            'issuer_name' => $mitra?->nama ?? 'PT Aldera Saddatech Karya',
            'template' => $this->resolveTemplateMeta($penawaran?->company_id, 'invoice', $mitra),
            'customer_name' => $penawaran?->to_company ?? $penawaran?->customer_nama,
            'customer_address' => $penawaran?->to_address,
            'invoice_number' => $invoice->nomor,
            'invoice_date' => $invoice->tanggal,
            'po_number' => $invoice->purchasingOrder?->nomor_po,
            'po_date' => $invoice->purchasingOrder?->tanggal_po,
            'items' => $penawaran?->items?->map(fn ($item) => [
                'nama' => $item->nama,
                'rincian' => $item->rincian,
                'qty' => (float) $item->qty,
                'satuan' => $item->satuan,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ])->values()->all() ?? [],
            'subtotal' => (float) ($penawaran?->subtotal ?? 0),
            'tax_percent' => (float) ($penawaran?->tax_percent ?? 0),
            'tax_amount' => (float) ($penawaran?->tax_amount ?? 0),
            'total' => (float) ($penawaran?->total ?? $invoice->total ?? 0),
            'payment_status' => $invoice->payment_status,
            'payment_date' => $invoice->payment_date,
            'signature_role' => $penawaran?->signature_role,
            'creator_name' => $invoice->creator?->name,
        ];
    }

    public function forSuratJalan(SuratJalan $suratJalan): array
    {
        $suratJalan->loadMissing(['invoice.penawaran.company', 'invoice.penawaran.mitra', 'invoice.penawaran.items', 'invoice.purchasingOrder']);
        $invoice = $suratJalan->invoice;
        $penawaran = $invoice?->penawaran;
        $mitra = $penawaran?->mitra;

        return [
            'template' => $this->resolveTemplateMeta($penawaran?->company_id, 'surat_jalan', $mitra),
            'invoice_number' => $invoice?->nomor,
            'invoice_date' => $invoice?->tanggal,
            'customer_name' => $penawaran?->to_company ?? $penawaran?->customer_nama,
            'customer_address' => $penawaran?->to_address,
            'sender_name' => $suratJalan->pemberi_nama,
            'sender_title' => $suratJalan->pemberi_jabatan,
            'sender_address' => $suratJalan->pemberi_alamat,
            'receiver_name' => $suratJalan->penerima_nama,
            'receiver_phone' => $suratJalan->penerima_hp,
            'city_date_manual' => $suratJalan->kota_tanggal_manual,
            'items' => $penawaran?->items?->map(fn ($item) => [
                'nama' => $item->nama,
                'rincian' => $item->rincian,
                'qty' => (float) $item->qty,
            ])->values()->all() ?? [],
        ];
    }

    public function forBeritaAcara(BeritaAcara $beritaAcara): array
    {
        $beritaAcara->loadMissing(['invoice.penawaran.company', 'invoice.penawaran.mitra', 'invoice.penawaran.items', 'invoice.purchasingOrder']);
        $invoice = $beritaAcara->invoice;
        $penawaran = $invoice?->penawaran;
        $po = $invoice?->purchasingOrder;
        $mitra = $penawaran?->mitra;
        $company = $penawaran?->company;
        $companyAddress = trim((string) ($company?->address ?? ''));

        return [
            'template' => $this->resolveTemplateMeta($penawaran?->company_id, 'berita_acara', $mitra),
            'company' => [
                'id' => $company?->id,
                'name' => $company?->name,
                'address' => $company?->address,
                'logo' => $company?->logo,
            ],
            'nomor' => $beritaAcara->nomor,
            'invoice_number' => $invoice?->nomor,
            'invoice_date' => $invoice?->tanggal,
            'customer_name' => $penawaran?->to_company ?? $penawaran?->customer_nama,
            'customer_address' => $penawaran?->to_address,
            'po_number' => $po?->nomor_po,
            'subject' => $beritaAcara->perihal,
            'nomor_perjanjian' => $beritaAcara->nomor_perjanjian ?: $po?->nomor_po,
            'tanggal_teks_manual' => $beritaAcara->tanggal_teks_manual,
            'tanggal' => $beritaAcara->tanggal,
            'pihak_pertama_nama' => $beritaAcara->pihak_pertama_nama ?: ($penawaran?->to_company ?? $penawaran?->customer_nama),
            'pihak_pertama_alamat' => $beritaAcara->pihak_pertama_alamat ?: $penawaran?->to_address,
            'pihak_kedua_nama' => $beritaAcara->pihak_kedua_nama ?: ($company?->name ?: 'PT Aldera Saddatech Karya'),
            'pihak_kedua_alamat' => $beritaAcara->pihak_kedua_alamat ?: (
                $companyAddress !== '' && $companyAddress !== '-'
                    ? $companyAddress
                    : 'Link. Acing Baru RT 001 RW 007, Kelurahan Masigit, Kecamatan Jombang, Kota Cilegon, Provinsi Banten.'
            ),
            'pekerjaan_manual' => $beritaAcara->pekerjaan_manual,
            'periode_manual' => $beritaAcara->periode_manual,
            'predikat_manual' => $beritaAcara->predikat_manual,
            'signature_role' => $penawaran?->signature_role,
            'closing_note' => $beritaAcara->keterangan_akhir,
            'city_date_manual' => $beritaAcara->kota_tanggal_manual,
            'items' => $penawaran?->items?->map(fn ($item) => [
                'nama' => $item->nama,
                'rincian' => $item->rincian,
                'qty' => (float) $item->qty,
            ])->values()->all() ?? [],
        ];
    }

    public function forNotaToko(NotaToko $notaToko): array
    {
        $notaToko->loadMissing(['company', 'items', 'user']);

        return [
            'company' => [
                'id' => $notaToko->company?->id,
                'name' => $notaToko->company?->name,
                'address' => $notaToko->company?->address,
                'logo' => $notaToko->company?->logo,
            ],
            'template' => $this->resolveTemplateMeta($notaToko->company_id, 'nota_toko'),
            'nomor' => $notaToko->nomor,
            'tanggal' => $notaToko->tanggal,
            'customer_name' => $notaToko->customer_nama,
            'customer_email' => $notaToko->customer_email,
            'address' => $notaToko->alamat,
            'notes' => $notaToko->keterangan,
            'payment_status' => $notaToko->payment_status,
            'payment_date' => $notaToko->payment_date,
            'creator_name' => $notaToko->user?->name,
            'items' => $notaToko->items->map(fn ($item) => [
                'nama' => $item->nama,
                'qty' => (float) $item->qty,
                'satuan' => $item->satuan,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ])->values()->all(),
            'subtotal' => (float) $notaToko->subtotal,
            'tax_percent' => (float) $notaToko->tax_percent,
            'tax_amount' => (float) $notaToko->tax_amount,
            'total' => (float) $notaToko->total,
            'payment_status' => $notaToko->payment_status,
            'payment_date' => $notaToko->payment_date,
        ];
    }

    private function resolveTemplateMeta(?int $companyId, string $documentType, ?Mitra $mitra = null): array
    {
        $path = $this->templateResolver->resolveTemplatePath($companyId, $documentType, $mitra);

        return [
            'scope' => $mitra ? 'mitra' : 'company',
            'path' => $path,
            'url' => $path ? route('document-templates.preview', ['path' => $path]) : null,
        ];
    }
}
