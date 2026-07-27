<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('berita_acaras', function (Blueprint $table) {
            $table->string('nomor_perjanjian')->nullable()->after('perihal');
            $table->string('tanggal_teks_manual')->nullable()->after('nomor_perjanjian');
            $table->string('pihak_pertama_nama')->nullable()->after('tanggal_teks_manual');
            $table->longText('pihak_pertama_alamat')->nullable()->after('pihak_pertama_nama');
            $table->string('pihak_kedua_nama')->nullable()->after('pihak_pertama_alamat');
            $table->longText('pihak_kedua_alamat')->nullable()->after('pihak_kedua_nama');
            $table->string('pekerjaan_manual')->nullable()->after('pihak_kedua_alamat');
            $table->string('periode_manual')->nullable()->after('pekerjaan_manual');
            $table->string('predikat_manual')->nullable()->after('periode_manual');
        });
    }

    public function down(): void
    {
        Schema::table('berita_acaras', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_perjanjian',
                'tanggal_teks_manual',
                'pihak_pertama_nama',
                'pihak_pertama_alamat',
                'pihak_kedua_nama',
                'pihak_kedua_alamat',
                'pekerjaan_manual',
                'periode_manual',
                'predikat_manual',
            ]);
        });
    }
};
