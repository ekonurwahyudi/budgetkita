<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian_asets', function (Blueprint $table) {
            $table->json('foto_aset')->nullable()->after('eviden');
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_asets', function (Blueprint $table) {
            $table->dropColumn('foto_aset');
        });
    }
};