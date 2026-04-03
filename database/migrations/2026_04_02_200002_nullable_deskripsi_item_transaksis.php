<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_transaksis', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('item_transaksis', function (Blueprint $table) {
            $table->text('deskripsi')->nullable(false)->change();
        });
    }
};
