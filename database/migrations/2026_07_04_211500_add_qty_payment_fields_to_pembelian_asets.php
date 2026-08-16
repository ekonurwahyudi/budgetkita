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
            $table->decimal('qty', 12, 2)->default(1)->after('tgl_pembelian');
            $table->decimal('harga_satuan', 15, 2)->default(0)->after('qty');
            $table->string('status_pembayaran', 20)->default('lunas')->after('account_bank_id');
            $table->decimal('nominal_dibayar', 15, 2)->default(0)->after('status_pembayaran');
            $table->foreignUuid('hutang_piutang_id')->nullable()->after('nominal_dibayar')->constrained('hutang_piutangs')->nullOnDelete();
        });

        DB::table('pembelian_asets')->update([
            'qty' => 1,
            'harga_satuan' => DB::raw('nominal_pembelian'),
            'nominal_dibayar' => DB::raw('nominal_pembelian'),
        ]);
    }

    public function down(): void
    {
        Schema::table('pembelian_asets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hutang_piutang_id');
            $table->dropColumn(['qty', 'harga_satuan', 'status_pembayaran', 'nominal_dibayar']);
        });
    }
};
