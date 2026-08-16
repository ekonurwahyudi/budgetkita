<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemberian_pakans', function (Blueprint $table) {
            $table->uuid('item_persediaan_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pemberian_pakans', function (Blueprint $table) {
            $table->uuid('item_persediaan_id')->nullable(false)->change();
        });
    }
};