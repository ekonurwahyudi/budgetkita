<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian_asets', function (Blueprint $table) {
            $table->integer('qty_tersedia')->default(0)->after('qty');
            $table->integer('qty_rusak')->default(0)->after('qty_tersedia');
        });

        DB::table('pembelian_asets')->update([
            'qty_tersedia' => DB::raw('qty'),
            'qty_rusak' => 0,
        ]);

        Schema::create('penjualan_asets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tambak_id')->nullable()->constrained('tambaks')->nullOnDelete();
            $table->foreignUuid('pembelian_aset_id')->constrained('pembelian_asets')->cascadeOnDelete();
            $table->string('nomor_transaksi')->unique();
            $table->date('tgl_penjualan');
            $table->integer('qty');
            $table->string('kondisi', 20)->default('baik');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total_penjualan', 15, 2);
            $table->string('pembeli')->nullable();
            $table->string('status_pembayaran', 20)->default('lunas');
            $table->decimal('nominal_dibayar', 15, 2)->default(0);
            $table->foreignUuid('account_bank_id')->nullable()->constrained('account_banks')->nullOnDelete();
            $table->foreignUuid('piutang_id')->nullable()->constrained('hutang_piutangs')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_asets');

        Schema::table('pembelian_asets', function (Blueprint $table) {
            $table->dropColumn(['qty_tersedia', 'qty_rusak']);
        });
    }
};
