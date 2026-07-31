<?php

namespace App\Services;

use App\Models\FakturPajak;
use App\Models\Invoice;
use App\Models\NotaToko;
use App\Models\Penawaran;

class DashboardDataService
{
    public function forCompany(int $companyId): array
    {
        $penawaranQuery = Penawaran::where('company_id', $companyId);
        $invoiceQuery = Invoice::whereHas('penawaran', fn ($query) => $query->where('company_id', $companyId));
        $notaTokoQuery = NotaToko::where('company_id', $companyId);
        $fakturQuery = FakturPajak::whereHas('invoice.penawaran', fn ($query) => $query->where('company_id', $companyId));
        $unpaidInvoices = (clone $invoiceQuery)
            ->where('payment_status', 'unpaid')
            ->with(['penawaran:id,company_id,tax_percent,tax_amount']);

        return [
            'dashboardStatus' => [
                'penawaran' => [
                    'draft' => (clone $penawaranQuery)->where('status', 'draft')->count(),
                    'submitted' => (clone $penawaranQuery)->where('status', 'submitted')->count(),
                    'approved' => (clone $penawaranQuery)->where('status', 'approved')->count(),
                    'rejected' => (clone $penawaranQuery)->where('status', 'rejected')->count(),
                ],
                'purchasing_order' => [
                    'menunggu_upload' => (clone $penawaranQuery)->where('status', 'approved')->whereDoesntHave('purchasingOrder')->count(),
                    'sudah_upload' => (clone $penawaranQuery)->whereHas('purchasingOrder')->count(),
                ],
                'invoice' => [
                    'belum_dibayar' => (clone $invoiceQuery)->where('payment_status', 'unpaid')->count(),
                    'sudah_dibayar' => (clone $invoiceQuery)->where('payment_status', 'paid')->count(),
                ],
                'faktur_pajak' => [
                    'menunggu_upload' => (clone $invoiceQuery)->whereDoesntHave('fakturPajak')->count(),
                    'belum_dibayar' => (clone $fakturQuery)->where('payment_status', 'unpaid')->count(),
                    'sudah_dibayar' => (clone $fakturQuery)->where('payment_status', 'paid')->count(),
                ],
            ],
            'dashboardFinancial' => [
                'total_semua' => (clone $invoiceQuery)->sum('total'),
                'total_sudah_dibayar' => (clone $invoiceQuery)->where('payment_status', 'paid')->sum('total'),
                'total_belum_dibayar' => (clone $invoiceQuery)->where('payment_status', 'unpaid')->sum('total'),
                'pajak_belum_dibayar' => $unpaidInvoices->get()->sum(fn (Invoice $invoice) => (float) ($invoice->penawaran?->tax_amount ?? 0)),
                'jumlah_semua' => (clone $invoiceQuery)->count(),
                'jumlah_sudah_dibayar' => (clone $invoiceQuery)->where('payment_status', 'paid')->count(),
                'jumlah_belum_dibayar' => (clone $invoiceQuery)->where('payment_status', 'unpaid')->count(),
            ],
            'dashboardNotaToko' => [
                'total_semua' => (clone $notaTokoQuery)->sum('total'),
                'total_sudah_dibayar' => (clone $notaTokoQuery)->where('payment_status', 'paid')->sum('total'),
                'total_belum_dibayar' => (clone $notaTokoQuery)->where('payment_status', 'unpaid')->sum('total'),
                'jumlah_semua' => (clone $notaTokoQuery)->count(),
                'jumlah_sudah_dibayar' => (clone $notaTokoQuery)->where('payment_status', 'paid')->count(),
                'jumlah_belum_dibayar' => (clone $notaTokoQuery)->where('payment_status', 'unpaid')->count(),
            ],
        ];
    }
}
