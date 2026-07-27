<?php

namespace Tests\Feature;

use App\Models\BeritaAcara;
use App\Models\Company;
use App\Models\Customer;
use App\Models\FakturPajak;
use App\Models\Invoice;
use App\Models\Penawaran;
use App\Models\PurchasingOrder;
use App\Models\SuratJalan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_delete_removes_related_docs_and_resets_penawaran_to_draft(): void
    {
        Storage::fake('public');

        $company = Company::create([
            'name' => 'PT Askarya',
            'address' => 'Jl. Testing No. 1',
            'logo' => null,
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $customer = Customer::create([
            'company_id' => $company->id,
            'nama' => 'PT Customer',
            'alamat' => 'Jl. Pelanggan No. 2',
            'no_hp' => '08123456789',
            'email' => 'customer@example.com',
        ]);

        $penawaran = Penawaran::create([
            'company_id' => $company->id,
            'mitra_id' => null,
            'user_id' => $user->id,
            'nomor' => 'PNW/2026/VI/001-ASK',
            'tanggal' => '2026-06-19',
            'customer_nama' => $customer->nama,
            'to_company' => $customer->nama,
            'to_address' => $customer->alamat,
            'jenis_kontrak' => 'satuan',
            'signature_role' => 'Direktur',
            'keterangan' => null,
            'subtotal' => 100000,
            'tax_percent' => 11,
            'tax_amount' => 11000,
            'total' => 111000,
            'status' => 'approved',
            'invoice_date' => '2026-06-19',
            'invoice_number' => 'INV/2026/VI/001-ASK',
            'invoice_sequence' => 1,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $poPath = 'purchasing-orders/sample-po.pdf';
        $fakturPath = 'faktur-pajaks/sample-faktur.pdf';
        Storage::disk('public')->put($poPath, 'po-file');
        Storage::disk('public')->put($fakturPath, 'faktur-file');

        $purchasingOrder = PurchasingOrder::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'dokumen_path' => $poPath,
            'dokumen_name' => 'sample-po.pdf',
            'nomor_po' => 'PO/2026/VI/001',
            'tanggal_po' => '2026-06-19',
            'uploaded_by' => $user->id,
            'uploaded_at' => now(),
        ]);

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'purchasing_order_id' => $purchasingOrder->id,
            'nomor' => 'INV/2026/VI/001-ASK',
            'tanggal' => '2026-06-19',
            'sequence' => 1,
            'total' => 111000,
            'created_by' => $user->id,
        ]);

        SuratJalan::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'nomor' => 'SJ/2026/06/001-ASK',
            'tanggal' => '2026-06-19',
            'created_by' => $user->id,
        ]);

        BeritaAcara::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'nomor' => 'BA/2026/06/001-ASK',
            'tanggal' => '2026-06-19',
            'perihal' => 'Berita Acara',
            'created_by' => $user->id,
        ]);

        FakturPajak::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'dokumen_path' => $fakturPath,
            'dokumen_name' => 'sample-faktur.pdf',
            'uploaded_by' => $user->id,
            'uploaded_at' => now(),
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($user);

        $this->delete(route('invoice.destroy', $invoice))
            ->assertRedirect(route('penawaran.show', $penawaran));

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('purchasing_orders', ['id' => $purchasingOrder->id]);
        $this->assertDatabaseMissing('surat_jalans', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('berita_acaras', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('faktur_pajaks', ['invoice_id' => $invoice->id]);

        $this->assertDatabaseHas('penawarans', [
            'id' => $penawaran->id,
            'status' => 'draft',
            'invoice_date' => null,
            'invoice_number' => null,
            'invoice_sequence' => null,
            'approved_by' => null,
        ]);

        Storage::disk('public')->assertMissing($poPath);
        Storage::disk('public')->assertMissing($fakturPath);
    }
}
