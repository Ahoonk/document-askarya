<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Penawaran;
use App\Models\PurchasingOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurchasingOrderPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchasing_order_preview_is_served_from_controller_route(): void
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
        ]);

        Storage::disk('public')->put('purchasing-orders/sample-po.pdf', '%PDF-1.4 fake pdf');

        $po = PurchasingOrder::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'dokumen_path' => 'purchasing-orders/sample-po.pdf',
            'dokumen_name' => 'sample-po.pdf',
            'nomor_po' => 'PO/2026/VI/001',
            'tanggal_po' => '2026-06-19',
            'uploaded_by' => $user->id,
            'uploaded_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('purchasing-order.preview', $po));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
