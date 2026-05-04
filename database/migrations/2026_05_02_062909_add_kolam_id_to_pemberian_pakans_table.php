<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemberian_pakans', function (Blueprint $table) {
            $table->uuid('kolam_id')->nullable()->after('siklus_id');
            $table->foreign('kolam_id')->references('id')->on('kolams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pemberian_pakans', function (Blueprint $table) {
            $table->dropForeign(['kolam_id']);
            $table->dropColumn('kolam_id');
        });
    }
};