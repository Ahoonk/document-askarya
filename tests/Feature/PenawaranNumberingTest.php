<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Penawaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenawaranNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_penawaran_number_uses_new_format_and_increments_by_month(): void
    {
        [$company, $user, $customer] = $this->createCompanyFixture();

        $this->actingAs($user);

        $payload = $this->penawaranPayload($customer->nama, $customer->alamat, '2026-06-19');

        $this->post(route('penawaran.store'), $payload)->assertRedirect();
        $this->assertDatabaseHas('penawarans', [
            'company_id' => $company->id,
            'nomor' => 'PNW/2026/VI/001-ASK',
            'tanggal' => '2026-06-19 00:00:00',
        ]);

        $this->post(route('penawaran.store'), $payload)->assertRedirect();
        $this->assertDatabaseHas('penawarans', [
            'company_id' => $company->id,
            'nomor' => 'PNW/2026/VI/002-ASK',
            'tanggal' => '2026-06-19 00:00:00',
        ]);
    }

    public function test_penawaran_number_changes_when_date_moves_to_another_month(): void
    {
        [$company, $user, $customer] = $this->createCompanyFixture();

        $this->actingAs($user);

        $payload = $this->penawaranPayload($customer->nama, $customer->alamat, '2026-06-19');
        $this->post(route('penawaran.store'), $payload)->assertRedirect();

        $penawaran = Penawaran::query()->where('company_id', $company->id)->firstOrFail();

        $this->put(route('penawaran.update', $penawaran), $this->penawaranPayload($customer->nama, $customer->alamat, '2026-07-05'))
            ->assertRedirect();

        $this->assertDatabaseHas('penawarans', [
            'id' => $penawaran->id,
            'company_id' => $company->id,
            'nomor' => 'PNW/2026/VII/001-ASK',
            'tanggal' => '2026-07-05 00:00:00',
        ]);
    }

    private function createCompanyFixture(): array
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

        return [$company, $user, $customer];
    }

    private function penawaranPayload(string $toCompany, string $toAddress, string $tanggal): array
    {
        return [
            'mitra_id' => null,
            'tanggal' => $tanggal,
            'to_company' => $toCompany,
            'to_address' => $toAddress,
            'jenis_kontrak' => 'satuan',
            'signature_role' => 'Direktur',
            'keterangan' => '1. Masa berlaku penawaran 7 Hari',
            'tax_percent' => 11,
            'status' => 'draft',
            'items' => [
                [
                    'nama' => 'Jasa Konsultasi',
                    'rincian' => null,
                    'qty' => 1,
                    'satuan' => 'item',
                    'unit_price' => 100000,
                ],
            ],
        ];
    }
}
