<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kolams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('siklus_id')->constrained('sikluses')->cascadeOnDelete();
            $table->foreignUuid('blok_id')->constrained('bloks')->cascadeOnDelete();
            $table->string('nama_kolam');
            $table->date('tgl_berdiri')->nullable();
            $table->integer('total_tebar')->nullable();
            $table->enum('status', ['aktif', 'selesai', 'batal'])->default('aktif');
            $table->timestamps();
        });

        Schema::create('kolam_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kolam_id')->constrained('kolams')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('kolam_parameters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kolam_id')->constrained('kolams')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tgl_parameter');
            $table->decimal('ph_pagi', 5, 2)->nullable();
            $table->decimal('ph_sore', 5, 2)->nullable();
            $table->decimal('do_pagi', 5, 2)->nullable();
            $table->decimal('do_sore', 5, 2)->nullable();
            $table->decimal('suhu_pagi', 5, 2)->nullable();
            $table->decimal('suhu_sore', 5, 2)->nullable();
            $table->decimal('kecerahan_pagi', 5, 2)->nullable();
            $table->decimal('kecerahan_sore', 5, 2)->nullable();
            $table->decimal('salinitas', 5, 2)->nullable();
            $table->decimal('tinggi_air', 5, 2)->nullable();
            $table->string('warna_air')->nullable();
            $table->decimal('alk', 5, 2)->nullable();
            $table->decimal('ca', 5, 2)->nullable();
            $table->decimal('mg', 5, 2)->nullable();
            $table->decimal('mbw', 5, 2)->nullable();
            $table->decimal('masa', 5, 2)->nullable();
            $table->decimal('sr', 5, 2)->nullable();
            $table->decimal('pcr', 5, 2)->nullable();
            $table->text('perlakuan_harian')->nullable();
            $table->enum('status', ['normal', 'perhatian', 'kritis'])->default('normal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kolam_parameters');
        Schema::dropIfExists('kolam_users');
        Schema::dropIfExists('kolams');
    }
};
