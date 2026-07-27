<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Penawaran;
use App\Models\SuratJalan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratJalanPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_surat_jalan_preview_is_served_from_controller_route(): void
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
        ]);

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'nomor' => 'INV/2026/VI/001-ASK',
            'tanggal' => '2026-06-19',
            'sequence' => 1,
            'total' => 111000,
            'created_by' => $user->id,
        ]);

        $suratJalan = SuratJalan::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'nomor' => 'SJ/2026/VI/001-ASK',
            'tanggal' => '2026-06-19',
            'pemberi_nama' => 'Admin',
            'pemberi_jabatan' => 'Direktur',
            'pemberi_alamat' => 'Jl. Testing No. 1',
            'penerima_nama' => 'Warehouse',
            'penerima_hp' => '08111111111',
            'kota_tanggal_manual' => '2026-06-19',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('surat-jalan.preview', $suratJalan));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
