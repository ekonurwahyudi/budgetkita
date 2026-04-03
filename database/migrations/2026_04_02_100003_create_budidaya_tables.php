<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tambaks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_tambak');
            $table->string('lokasi');
            $table->text('alamat');
            $table->decimal('total_lahan', 10, 2)->nullable();
            $table->date('didirikan_pada')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('bloks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tambak_id')->constrained('tambaks')->cascadeOnDelete();
            $table->string('nama_blok');
            $table->date('didirikan_pada')->nullable();
            $table->integer('jumlah_anco')->nullable();
            $table->decimal('panjang', 10, 2)->nullable();
            $table->decimal('lebar', 10, 2)->nullable();
            $table->decimal('kedalaman', 10, 2)->nullable();
            $table->enum('status_blok', ['aktif', 'nonaktif', 'maintenance'])->default('aktif');
            $table->timestamps();
        });

        Schema::create('sikluses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('blok_id')->constrained('bloks')->cascadeOnDelete();
            $table->string('nama_siklus');
            $table->date('tgl_siklus');
            $table->integer('lama_persiapan')->nullable();
            $table->date('tgl_tebar')->nullable();
            $table->integer('total_tebar')->nullable();
            $table->string('spesies_udang')->nullable();
            $table->integer('umur_awal')->nullable();
            $table->decimal('kecerahan', 5, 2)->nullable();
            $table->decimal('suhu', 5, 2)->nullable();
            $table->decimal('do_level', 5, 2)->nullable();
            $table->decimal('salinitas', 5, 2)->nullable();
            $table->decimal('ph_pagi', 5, 2)->nullable();
            $table->decimal('ph_sore', 5, 2)->nullable();
            $table->decimal('selisih_ph', 5, 2)->nullable();
            $table->decimal('fcr', 5, 2)->nullable();
            $table->decimal('adg', 5, 2)->nullable();
            $table->decimal('sr', 5, 2)->nullable();
            $table->decimal('mbw', 5, 2)->nullable();
            $table->decimal('size', 5, 2)->nullable();
            $table->enum('status', ['aktif', 'selesai', 'gagal'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sikluses');
        Schema::dropIfExists('bloks');
        Schema::dropIfExists('tambaks');
    }
};
