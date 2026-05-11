<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persediaans', function (Blueprint $table) {
            $table->decimal('minimum_stok', 10, 2)->nullable()->after('qty');
        });
    }

    public function down(): void
    {
        Schema::table('persediaans', function (Blueprint $table) {
            $table->dropColumn('minimum_stok');
        });
    }
};
