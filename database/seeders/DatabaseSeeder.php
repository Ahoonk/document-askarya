<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['name' => 'PT Aldera Saddatech Karya'],
            ['address' => '-', 'logo' => null]
        );

        User::firstOrCreate(
            ['email' => 'admin@askarya.test'],
            [
                'company_id' => $company->id,
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin2@askarya.test'],
            [
                'company_id' => $company->id,
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::factory()->count(3)->create([
            'company_id' => $company->id,
        ]);

        Customer::firstOrCreate(
            ['company_id' => $company->id, 'nama' => 'PT Maju Jaya Abadi'],
            ['alamat' => 'Jl. Merdeka No. 10, Cilegon', 'no_hp' => '081234567890', 'email' => 'procurement@majujaya.test']
        );

        Customer::firstOrCreate(
            ['company_id' => $company->id, 'nama' => 'CV Sinar Teknik'],
            ['alamat' => 'Jl. Industri No. 22, Serang', 'no_hp' => '081298765432', 'email' => 'admin@sinarteknik.test']
        );

        Mitra::firstOrCreate(
            ['company_id' => $company->id, 'nama' => 'Mitra Aldera'],
            [
                'email' => 'mitra@aldera.test',
                'alamat' => 'Kawasan Industri Cilegon',
                'nomor_penawaran' => null,
                'nomor_invoice' => null,
                'nomor_surat_jalan' => null,
                'nomor_berita_acara' => null,
            ]
        );
    }
}
