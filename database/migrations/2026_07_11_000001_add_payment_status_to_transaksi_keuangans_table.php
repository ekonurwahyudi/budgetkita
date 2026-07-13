<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_keuangans', function (Blueprint $table) {
            $table->enum('status_pembayaran', ['lunas', 'hutang', 'sebagian'])->default('lunas')->after('account_bank_id');
            $table->decimal('nominal_dibayar', 15, 2)->default(0)->after('status_pembayaran');
            $table->foreignUuid('hutang_piutang_id')->nullable()->after('nominal_dibayar')->constrained('hutang_piutangs');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_keuangans', function (Blueprint $table) {
            $table->dropForeign(['hutang_piutang_id']);
            $table->dropColumn(['status_pembayaran', 'nominal_dibayar', 'hutang_piutang_id']);
        });
    }
};
