<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gaji_karyawans', function (Blueprint $table) {
            $table->json('eviden')->nullable()->after('account_bank_id');
        });

        Schema::table('hutang_piutangs', function (Blueprint $table) {
            $table->json('eviden')->nullable()->after('account_bank_id');
        });

        // Investasi sudah punya eviden string, ubah ke json untuk konsistensi multi-file
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE investasis ALTER COLUMN eviden TYPE json USING CASE WHEN eviden IS NULL THEN NULL ELSE to_json(eviden) END');
    }

    public function down(): void
    {
        Schema::table('gaji_karyawans', function (Blueprint $table) {
            $table->dropColumn('eviden');
        });
        Schema::table('hutang_piutangs', function (Blueprint $table) {
            $table->dropColumn('eviden');
        });
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE investasis ALTER COLUMN eviden TYPE varchar(255) USING eviden::text');
    }
};
