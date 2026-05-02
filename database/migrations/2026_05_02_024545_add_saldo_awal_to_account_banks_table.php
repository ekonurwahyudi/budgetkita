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
        Schema::table('account_banks', function (Blueprint $table) {
            $table->decimal('saldo_awal', 15, 2)->nullable()->after('saldo')->comment('Saldo awal saat bank dibuat, untuk perhitungan rekonsiliasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_banks', function (Blueprint $table) {
            $table->dropColumn('saldo_awal');
        });
    }
};
