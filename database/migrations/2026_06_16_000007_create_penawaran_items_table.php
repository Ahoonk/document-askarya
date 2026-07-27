<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penawaran_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penawaran_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->longText('rincian')->nullable();
            $table->decimal('qty', 15, 2);
            $table->string('satuan');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penawaran_items');
    }
};
