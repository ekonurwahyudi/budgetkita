<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_banks', function (Blueprint $table) {
            $table->renameColumn('deskripsi', 'nama_bank');
            $table->string('nama_pemilik')->nullable()->after('nama_bank');
            $table->string('nomor_rekening')->nullable()->after('nama_pemilik');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('saldo');
        });
    }

    public function down(): void
    {
        Schema::table('account_banks', function (Blueprint $table) {
            $table->renameColumn('nama_bank', 'deskripsi');
            $table->dropColumn(['nama_pemilik', 'nomor_rekening', 'status']);
        });
    }
};
