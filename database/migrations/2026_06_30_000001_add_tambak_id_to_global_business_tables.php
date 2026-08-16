<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'account_banks',
        'investasis',
        'gaji_karyawans',
        'hutang_piutangs',
        'pembelian_persediaans',
        'pembelian_asets',
        'persediaans',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasColumn($tableName, 'tambak_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignUuid('tambak_id')->nullable()->after('id')->constrained('tambaks')->nullOnDelete();
                });
            }
        }

        $defaultTambakId = DB::table('tambaks')->orderBy('created_at')->value('id');

        if ($defaultTambakId) {
            foreach ($this->tables as $tableName) {
                DB::table($tableName)
                    ->whereNull('tambak_id')
                    ->update(['tambak_id' => $defaultTambakId]);
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            if (Schema::hasColumn($tableName, 'tambak_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('tambak_id');
                });
            }
        }
    }
};
