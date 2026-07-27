<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('prefix')->nullable();
            $table->boolean('year_mode')->default(true);
            $table->boolean('month_mode')->default(true);
            $table->unsignedInteger('counter')->default(0);
            $table->unsignedTinyInteger('padding')->default(3);
            $table->string('suffix')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_series');
    }
};
