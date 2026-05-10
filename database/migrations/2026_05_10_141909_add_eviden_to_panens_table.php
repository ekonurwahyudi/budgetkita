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
        Schema::table('panens', function (Blueprint $table) {
            $table->json('eviden')->nullable()->after('account_bank_id');
        });
    }

    public function down(): void
    {
        Schema::table('panens', function (Blueprint $table) {
            $table->dropColumn('eviden');
        });
    }
};
