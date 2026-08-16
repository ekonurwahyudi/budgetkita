<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pembelian_asets', function (Blueprint $table) {
            $table->string('nomor_transaksi')->nullable()->unique()->after('id');
        });

        DB::table('pembelian_asets')->whereNull('nomor_transaksi')->eachById(function ($row, $i) {
            DB::table('pembelian_asets')->where('id', $row->id)->update([
                'nomor_transaksi' => sprintf('INVA-%s-%06d', date('y'), $i + 1),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_asets', function (Blueprint $table) {
            $table->dropColumn('nomor_transaksi');
        });
    }
};
