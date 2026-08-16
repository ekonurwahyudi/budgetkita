<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian_persediaans', function (Blueprint $table) {
            if (!Schema::hasColumn('pembelian_persediaans', 'blok_id')) {
                $table->foreignUuid('blok_id')->nullable()->after('tambak_id')->constrained('bloks')->nullOnDelete();
            }

            if (!Schema::hasColumn('pembelian_persediaans', 'siklus_id')) {
                $table->foreignUuid('siklus_id')->nullable()->after('blok_id')->constrained('sikluses')->nullOnDelete();
            }

            if (!Schema::hasColumn('pembelian_persediaans', 'status_pembayaran')) {
                $table->string('status_pembayaran', 20)->default('lunas')->after('account_bank_id');
            }

            if (!Schema::hasColumn('pembelian_persediaans', 'nominal_dibayar')) {
                $table->decimal('nominal_dibayar', 15, 2)->default(0)->after('status_pembayaran');
            }

            if (!Schema::hasColumn('pembelian_persediaans', 'hutang_piutang_id')) {
                $table->foreignUuid('hutang_piutang_id')->nullable()->after('nominal_dibayar')->constrained('hutang_piutangs')->nullOnDelete();
            }
        });

        DB::statement("
            UPDATE pembelian_persediaans p
            SET nominal_dibayar = COALESCE((
                SELECT SUM(i.harga_total)
                FROM pembelian_persediaan_items i
                WHERE i.pembelian_persediaan_id = p.id
            ), 0)
            WHERE p.nominal_dibayar = 0
        ");
    }

    public function down(): void
    {
        Schema::table('pembelian_persediaans', function (Blueprint $table) {
            if (Schema::hasColumn('pembelian_persediaans', 'hutang_piutang_id')) {
                $table->dropConstrainedForeignId('hutang_piutang_id');
            }

            foreach (['nominal_dibayar', 'status_pembayaran'] as $column) {
                if (Schema::hasColumn('pembelian_persediaans', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('pembelian_persediaans', 'siklus_id')) {
                $table->dropConstrainedForeignId('siklus_id');
            }

            if (Schema::hasColumn('pembelian_persediaans', 'blok_id')) {
                $table->dropConstrainedForeignId('blok_id');
            }
        });
    }
};
