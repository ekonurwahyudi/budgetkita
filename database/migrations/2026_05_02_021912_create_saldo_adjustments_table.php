<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('saldo_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_bank_id')->constrained('account_banks')->onDelete('cascade');
            $table->decimal('saldo_sebelumnya', 15, 2);
            $table->decimal('saldo_baru', 15, 2);
            $table->decimal('selisih', 15, 2);
            $table->string('jenis'); // tambah / kurang
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_adjustments');
    }
};
