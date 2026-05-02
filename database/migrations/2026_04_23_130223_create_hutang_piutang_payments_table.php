<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hutang_piutang_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hutang_piutang_id');
            $table->decimal('jumlah', 15, 2);
            $table->uuid('account_bank_id')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->foreign('hutang_piutang_id')->references('id')->on('hutang_piutangs')->onDelete('cascade');
            $table->foreign('account_bank_id')->references('id')->on('account_banks')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hutang_piutang_payments');
    }
};
