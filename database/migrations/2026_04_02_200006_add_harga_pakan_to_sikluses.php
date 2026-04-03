<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sikluses', function (Blueprint $table) {
            $table->decimal('harga_pakan', 15, 2)->nullable()->after('size')
                  ->comment('Estimasi harga pakan per kg untuk kalkulasi profit');
        });
    }

    public function down(): void
    {
        Schema::table('sikluses', function (Blueprint $table) {
            $table->dropColumn('harga_pakan');
        });
    }
};
