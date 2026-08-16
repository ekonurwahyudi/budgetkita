<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian_asets', function (Blueprint $table) {
            if (!Schema::hasColumn('pembelian_asets', 'blok_id')) {
                $table->foreignUuid('blok_id')->nullable()->after('tambak_id')->constrained('bloks')->nullOnDelete();
            }

            if (!Schema::hasColumn('pembelian_asets', 'siklus_id')) {
                $table->foreignUuid('siklus_id')->nullable()->after('blok_id')->constrained('sikluses')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_asets', function (Blueprint $table) {
            if (Schema::hasColumn('pembelian_asets', 'siklus_id')) {
                $table->dropConstrainedForeignId('siklus_id');
            }

            if (Schema::hasColumn('pembelian_asets', 'blok_id')) {
                $table->dropConstrainedForeignId('blok_id');
            }
        });
    }
};
