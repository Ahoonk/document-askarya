<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Models\Penawaran;
use App\Models\User;
use App\Services\DocumentSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PenawaranTemplatePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_penawaran_without_mitra_uses_company_template_preview(): void
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

        $templatePath = 'document-templates/penawaran-preview.pdf';
        Storage::disk('public')->put($templatePath, '%PDF-1.4 fake template');

        DocumentTemplate::create([
            'company_id' => $company->id,
            'document_type' => 'penawaran',
            'name' => 'Template Penawaran',
            'file_path' => $templatePath,
            'is_default' => true,
        ]);

        $penawaran = Penawaran::create([
            'company_id' => $company->id,
            'mitra_id' => null,
            'user_id' => $user->id,
            'nomor' => 'PNW/0001/VI/2026',
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
            'status' => 'draft',
        ]);

        $penawaran->items()->create([
            'nama' => 'Jasa Konsultasi',
            'rincian' => null,
            'qty' => 1,
            'satuan' => 'item',
            'unit_price' => 100000,
            'amount' => 100000,
        ]);

        $snapshot = app(DocumentSnapshotService::class)->forPenawaran($penawaran);

        $this->assertSame('company', $snapshot['template']['scope']);
        $this->assertSame($templatePath, $snapshot['template']['path']);
        $this->assertNotEmpty($snapshot['template']['url']);
        $this->assertStringContainsString('/document-templates/preview', $snapshot['template']['url']);
    }

    public function test_penawaran_preview_pdf_returns_filled_pdf_response(): void
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

        Storage::disk('public')->put('document-templates/penawaran-preview.pdf', '%PDF-1.4 fake template');

        DocumentTemplate::create([
            'company_id' => $company->id,
            'document_type' => 'penawaran',
            'name' => 'Template Penawaran',
            'file_path' => 'document-templates/penawaran-preview.pdf',
            'is_default' => true,
        ]);

        $penawaran = Penawaran::create([
            'company_id' => $company->id,
            'mitra_id' => null,
            'user_id' => $user->id,
            'nomor' => 'PNW/0001/VI/2026',
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
            'status' => 'draft',
        ]);

        $penawaran->items()->create([
            'nama' => 'Layanan Internet Bisnis',
            'rincian' => 'Periode Desember 2025',
            'qty' => 1,
            'satuan' => 'item',
            'unit_price' => 100000,
            'amount' => 100000,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('penawaran.preview-pdf', $penawaran->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertSee('Layanan Internet Bisnis', false);
        $response->assertSee('PT Askarya', false);
    }
}
