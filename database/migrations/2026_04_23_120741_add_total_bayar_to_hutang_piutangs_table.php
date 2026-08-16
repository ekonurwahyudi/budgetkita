<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hutang_piutangs', function (Blueprint $table) {
            $table->decimal('total_bayar', 15, 2)->nullable()->after('nominal');
        });
    }

    public function down(): void
    {
        Schema::table('hutang_piutangs', function (Blueprint $table) {
            $table->dropColumn('total_bayar');
        });
    }
};
