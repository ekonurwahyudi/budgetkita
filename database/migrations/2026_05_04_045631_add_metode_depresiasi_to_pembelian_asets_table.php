<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pembelian_asets', function (Blueprint $table) {
            $table->string('metode_depresiasi', 20)->default('garis_lurus')->after('nilai_residu');
            $table->decimal('persen_depresiasi', 5, 2)->nullable()->after('metode_depresiasi');
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_asets', function (Blueprint $table) {
            $table->dropColumn(['metode_depresiasi', 'persen_depresiasi']);
        });
    }
};
