<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Penawaran;
use App\Models\SuratJalan;
use App\Models\User;
use App\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_number_uses_new_format_and_increments_by_month(): void
    {
        [$company, $user, $customer, $penawaran] = $this->createCompanyFixture('2026-06-19');

        $service = app(DocumentNumberService::class);
        $firstNumber = $service->nextAlderaInvoice($company->id, '2026-06-19');

        Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'nomor' => $firstNumber,
            'tanggal' => '2026-06-19',
            'sequence' => 1,
            'total' => 111000,
            'created_by' => $user->id,
        ]);

        $secondNumber = $service->nextAlderaInvoice($company->id, '2026-06-19');

        $this->assertSame('INV/2026/VI/001-ASK', $firstNumber);
        $this->assertSame('INV/2026/VI/002-ASK', $secondNumber);
    }

    public function test_invoice_date_update_renumbers_invoice_and_surat_jalan(): void
    {
        [$company, $user, $customer, $penawaran] = $this->createCompanyFixture('2026-06-19');

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'nomor' => 'INV/2026/06/001-ASK',
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

        $this->actingAs($user);

        $this->post(route('invoice.update-print-date', $invoice), [
            'tanggal' => '2026-07-05',
        ])->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'nomor' => 'INV/2026/VII/001-ASK',
            'tanggal' => '2026-07-05 00:00:00',
        ]);

        $this->assertDatabaseHas('surat_jalans', [
            'invoice_id' => $invoice->id,
            'nomor' => 'SJ/2026/07/001-ASK',
            'tanggal' => '2026-07-05 00:00:00',
        ]);
    }

    private function createCompanyFixture(string $tanggal): array
    {
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
            'tanggal' => $tanggal,
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
        ]);

        return [$company, $user, $customer, $penawaran];
    }
}
