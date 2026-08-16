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
            $table->foreignUuid('kolam_id')->nullable()->after('siklus_id')->constrained('kolams');
        });
    }

    public function down(): void
    {
        Schema::table('panens', function (Blueprint $table) {
            $table->dropForeign(['kolam_id']);
            $table->dropColumn('kolam_id');
        });
    }
};
