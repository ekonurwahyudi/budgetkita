<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian_asets', function (Blueprint $table) {
            $table->foreignUuid('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->text('reject_reason')->nullable()->after('created_by');
        });

        Schema::table('pembelian_persediaans', function (Blueprint $table) {
            $table->foreignUuid('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->text('reject_reason')->nullable()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_persediaans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('reject_reason');
        });

        Schema::table('pembelian_asets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('reject_reason');
        });
    }
};
