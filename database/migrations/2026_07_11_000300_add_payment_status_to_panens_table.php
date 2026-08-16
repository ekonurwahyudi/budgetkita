<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE panens DROP CONSTRAINT panens_pembayaran_check");
        DB::statement("ALTER TABLE panens ADD CONSTRAINT panens_pembayaran_check CHECK (pembayaran IN ('lunas', 'piutang', 'sebagian'))");

        Schema::table('panens', function (Blueprint $table) {
            $table->decimal('nominal_dibayar', 15, 2)->default(0)->after('sisa_bayar');
            $table->foreignUuid('hutang_piutang_id')->nullable()->after('nominal_dibayar')->constrained('hutang_piutangs');
        });
    }

    public function down(): void
    {
        Schema::table('panens', function (Blueprint $table) {
            $table->dropForeign(['hutang_piutang_id']);
            $table->dropColumn(['nominal_dibayar', 'hutang_piutang_id']);
        });

        DB::statement("ALTER TABLE panens DROP CONSTRAINT panens_pembayaran_check");
        DB::statement("ALTER TABLE panens ADD CONSTRAINT panens_pembayaran_check CHECK (pembayaran IN ('lunas', 'piutang'))");
    }
};
