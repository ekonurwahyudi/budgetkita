<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable()->after('no_hp');
            $table->date('tgl_lahir')->nullable()->after('tempat_lahir');
            $table->string('nomor_rekening')->nullable()->after('tgl_lahir');
            $table->string('bank')->nullable()->after('nomor_rekening');
            $table->date('mulai_bekerja')->nullable()->after('bank');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tempat_lahir', 'tgl_lahir', 'nomor_rekening', 'bank', 'mulai_bekerja']);
        });
    }
};
