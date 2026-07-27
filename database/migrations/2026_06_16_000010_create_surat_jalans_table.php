<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_jalans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('nomor')->unique();
            $table->date('tanggal');
            $table->string('pemberi_nama')->nullable();
            $table->string('pemberi_jabatan')->nullable();
            $table->text('pemberi_alamat')->nullable();
            $table->string('penerima_nama')->nullable();
            $table->string('penerima_hp')->nullable();
            $table->date('kota_tanggal_manual')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('snapshot_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_jalans');
    }
};
