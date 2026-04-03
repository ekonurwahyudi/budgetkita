<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_keuangans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_transaksi')->unique();
            $table->enum('jenis_transaksi', ['uang_masuk', 'uang_keluar', 'cash_card']);
            $table->date('tgl_kwitansi');
            $table->text('aktivitas');
            $table->decimal('nominal', 15, 2);
            $table->foreignUuid('item_transaksi_id')->constrained('item_transaksis');
            $table->foreignUuid('kategori_transaksi_id')->constrained('kategori_transaksis');
            $table->foreignUuid('tambak_id')->constrained('tambaks');
            $table->foreignUuid('blok_id')->nullable()->constrained('bloks');
            $table->foreignUuid('siklus_id')->nullable()->constrained('sikluses');
            $table->foreignUuid('sumber_dana_id')->constrained('sumber_danas');
            $table->enum('jenis_pembayaran', ['cash', 'bank']);
            $table->foreignUuid('account_bank_id')->nullable()->constrained('account_banks');
            $table->string('eviden')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['awaiting_approval', 'proses', 'selesai', 'cancel', 'pending'])->default('awaiting_approval');
            $table->timestamps();
        });

        Schema::create('gaji_karyawans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_transaksi')->unique();
            $table->foreignUuid('user_id')->constrained('users');
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('upah_lembur', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('thp', 15, 2)->default(0);
            $table->decimal('pajak', 15, 2)->default(0);
            $table->decimal('bpjs', 15, 2)->default(0);
            $table->decimal('potongan', 15, 2)->default(0);
            $table->enum('jenis_pembayaran', ['cash', 'bank']);
            $table->foreignUuid('account_bank_id')->nullable()->constrained('account_banks');
            $table->enum('status', ['awaiting_approval', 'proses', 'selesai', 'cancel', 'pending'])->default('awaiting_approval');
            $table->timestamps();
        });

        Schema::create('investasis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_transaksi')->unique();
            $table->text('deskripsi');
            $table->decimal('nominal', 15, 2);
            $table->foreignUuid('kategori_investasi_id')->constrained('kategori_investasis');
            $table->string('eviden')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('jenis_pembayaran', ['cash', 'bank']);
            $table->foreignUuid('account_bank_id')->nullable()->constrained('account_banks');
            $table->enum('status', ['awaiting_approval', 'proses', 'selesai', 'cancel', 'pending'])->default('awaiting_approval');
            $table->timestamps();
        });

        Schema::create('hutang_piutangs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_transaksi')->unique();
            $table->enum('jenis', ['hutang', 'piutang']);
            $table->text('aktivitas');
            $table->foreignUuid('kategori_hutang_piutang_id')->constrained('kategori_hutang_piutangs');
            $table->decimal('nominal', 15, 2);
            $table->date('jatuh_tempo');
            $table->decimal('nominal_bayar', 15, 2)->default(0);
            $table->decimal('sisa_pembayaran', 15, 2)->default(0);
            $table->enum('jenis_pembayaran', ['cash', 'bank']);
            $table->foreignUuid('account_bank_id')->nullable()->constrained('account_banks');
            $table->text('catatan')->nullable();
            $table->enum('status', ['awaiting_approval', 'proses', 'selesai', 'cancel', 'pending'])->default('awaiting_approval');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hutang_piutangs');
        Schema::dropIfExists('investasis');
        Schema::dropIfExists('gaji_karyawans');
        Schema::dropIfExists('transaksi_keuangans');
    }
};
