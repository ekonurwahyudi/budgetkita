<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE transaksi_keuangans ALTER COLUMN eviden TYPE json USING CASE WHEN eviden IS NULL THEN NULL ELSE to_json(eviden) END');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE transaksi_keuangans ALTER COLUMN eviden TYPE varchar(255) USING eviden::text');
    }
};
