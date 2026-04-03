<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('siklus_id')->constrained('sikluses');
            $table->date('tgl_panen');
            $table->integer('umur');
            $table->decimal('ukuran', 10, 2);
            $table->decimal('total_berat', 10, 2);
            $table->decimal('harga_jual', 15, 2);
            $table->decimal('sisa_bayar', 15, 2)->default(0);
            $table->decimal('total_penjualan', 15, 2);
            $table->string('pembeli');
            $table->enum('tipe_panen', ['full', 'parsial', 'gagal']);
            $table->enum('jenis_pembayaran', ['cash', 'bank']);
            $table->foreignUuid('account_bank_id')->nullable()->constrained('account_banks');
            $table->enum('pembayaran', ['lunas', 'piutang']);
            $table->enum('status', ['awaiting_approval', 'proses', 'selesai', 'cancel', 'pending'])->default('awaiting_approval');
            $table->timestamps();
        });

        Schema::create('pemberian_pakans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('blok_id')->constrained('bloks');
            $table->foreignUuid('siklus_id')->constrained('sikluses');
            $table->timestamp('tgl_pakan');
            $table->decimal('jumlah_pakan', 10, 2);
            $table->foreignUuid('item_persediaan_id')->constrained('item_persediaans');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemberian_pakans');
        Schema::dropIfExists('panens');
    }
};
