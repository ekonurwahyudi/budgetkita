<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_investasis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_investasi')->unique();
            $table->text('deskripsi');
            $table->timestamps();
        });

        Schema::create('kategori_hutang_piutangs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_hutang_piutang')->unique();
            $table->text('deskripsi');
            $table->timestamps();
        });

        Schema::create('kategori_asets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_aset')->unique();
            $table->text('deskripsi');
            $table->timestamps();
        });

        Schema::create('account_banks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_account')->unique();
            $table->text('deskripsi');
            $table->decimal('saldo', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('auto_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('prefix');
            $table->integer('year');
            $table->integer('last_number')->default(0);
            $table->unique(['prefix', 'year']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_numbers');
        Schema::dropIfExists('account_banks');
        Schema::dropIfExists('kategori_asets');
        Schema::dropIfExists('kategori_hutang_piutangs');
        Schema::dropIfExists('kategori_investasis');
    }
};
