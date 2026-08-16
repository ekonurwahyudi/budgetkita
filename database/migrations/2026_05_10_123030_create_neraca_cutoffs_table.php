<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('neraca_cutoffs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->year('tahun');
            $table->date('tanggal_cutoff');
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('neraca_cutoffs');
    }
};
