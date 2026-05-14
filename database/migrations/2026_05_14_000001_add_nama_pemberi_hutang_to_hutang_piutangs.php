<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hutang_piutangs', function (Blueprint $table) {
            $table->string('nama_pemberi_hutang')->nullable()->after('jenis');
        });
    }

    public function down(): void
    {
        Schema::table('hutang_piutangs', function (Blueprint $table) {
            $table->dropColumn('nama_pemberi_hutang');
        });
    }
};
