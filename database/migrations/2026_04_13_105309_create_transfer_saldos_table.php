<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_saldos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dari_account_bank_id')->constrained('account_banks');
            $table->foreignUuid('ke_account_bank_id')->constrained('account_banks');
            $table->decimal('nominal', 15, 2);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_saldos');
    }
};
