<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('penawaran_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchasing_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nomor')->unique();
            $table->date('tanggal');
            $table->unsignedInteger('sequence');
            $table->decimal('total', 15, 2)->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->date('payment_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('snapshot_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
