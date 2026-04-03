<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persediaans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('item_persediaan_id')->constrained('item_persediaans');
            $table->decimal('qty', 10, 2)->default(0);
            $table->string('unit');
            $table->decimal('harga_per_unit', 15, 2)->default(0);
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('riwayat_persediaans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('persediaan_id')->constrained('persediaans')->cascadeOnDelete();
            $table->enum('jenis', ['penambahan', 'pengeluaran']);
            $table->decimal('qty_masuk', 10, 2)->default(0);
            $table->decimal('qty_keluar', 10, 2)->default(0);
            $table->foreignUuid('blok_id')->nullable()->constrained('bloks');
            $table->foreignUuid('siklus_id')->nullable()->constrained('sikluses');
            $table->decimal('harga_per_unit', 15, 2)->default(0);
            $table->decimal('harga_total', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('penyesuaian_persediaans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('persediaan_id')->constrained('persediaans')->cascadeOnDelete();
            $table->date('tgl_penyesuaian');
            $table->decimal('qty_sistem', 10, 2);
            $table->decimal('qty_fisik', 10, 2);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('pembelian_persediaans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_transaksi')->unique();
            $table->date('tgl_pembelian');
            $table->enum('jenis_pembayaran', ['cash', 'bank']);
            $table->foreignUuid('account_bank_id')->nullable()->constrained('account_banks');
            $table->string('eviden')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['awaiting_approval', 'proses', 'selesai', 'cancel', 'pending'])->default('awaiting_approval');
            $table->timestamps();
        });

        Schema::create('pembelian_persediaan_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pembelian_persediaan_id')->constrained('pembelian_persediaans')->cascadeOnDelete();
            $table->foreignUuid('item_persediaan_id')->constrained('item_persediaans');
            $table->decimal('qty', 10, 2);
            $table->string('satuan');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('harga_total', 15, 2);
            $table->timestamps();
        });

        Schema::create('pembelian_asets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_aset');
            $table->foreignUuid('kategori_aset_id')->constrained('kategori_asets');
            $table->date('tgl_pembelian');
            $table->decimal('nominal_pembelian', 15, 2);
            $table->integer('umur_manfaat');
            $table->decimal('nilai_residu', 15, 2)->default(0);
            $table->enum('jenis_pembayaran', ['cash', 'bank']);
            $table->foreignUuid('account_bank_id')->nullable()->constrained('account_banks');
            $table->enum('status', ['awaiting_approval', 'proses', 'selesai', 'cancel', 'pending'])->default('awaiting_approval');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_asets');
        Schema::dropIfExists('pembelian_persediaan_items');
        Schema::dropIfExists('pembelian_persediaans');
        Schema::dropIfExists('penyesuaian_persediaans');
        Schema::dropIfExists('riwayat_persediaans');
        Schema::dropIfExists('persediaans');
    }
};
