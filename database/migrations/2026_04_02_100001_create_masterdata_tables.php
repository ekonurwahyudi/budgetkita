<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_transaksis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_kategori')->unique();
            $table->text('deskripsi');
            $table->timestamps();
        });

        Schema::create('item_transaksis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kategori_transaksi_id')->constrained('kategori_transaksis')->cascadeOnDelete();
            $table->string('kode_item')->unique();
            $table->text('deskripsi');
            $table->timestamps();
        });

        Schema::create('sumber_danas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_sumber_dana')->unique();
            $table->text('deskripsi');
            $table->timestamps();
        });

        Schema::create('kategori_persediaans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_persediaan')->unique();
            $table->text('deskripsi');
            $table->timestamps();
        });

        Schema::create('item_persediaans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kategori_persediaan_id')->constrained('kategori_persediaans')->cascadeOnDelete();
            $table->string('kode_item_persediaan')->unique();
            $table->text('deskripsi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_persediaans');
        Schema::dropIfExists('kategori_persediaans');
        Schema::dropIfExists('sumber_danas');
        Schema::dropIfExists('item_transaksis');
        Schema::dropIfExists('kategori_transaksis');
    }
};
