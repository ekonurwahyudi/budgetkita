<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE kolam_users ALTER COLUMN id SET DEFAULT gen_random_uuid()');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE kolam_users ALTER COLUMN id DROP DEFAULT');
    }
};