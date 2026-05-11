<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sharing_revenues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_transaksi')->unique();
            $table->string('nama_penerima');
            $table->foreignUuid('blok_id')->constrained('bloks');
            $table->foreignUuid('siklus_id')->constrained('sikluses');
            $table->decimal('total_keuntungan', 15, 2)->default(0);
            $table->decimal('persentase', 8, 2)->default(0);
            $table->decimal('nominal', 15, 2)->default(0);
            $table->enum('jenis_pembayaran', ['cash', 'bank'])->default('bank');
            $table->foreignUuid('account_bank_id')->nullable()->constrained('account_banks');
            $table->json('eviden')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['awaiting_approval', 'proses', 'selesai', 'cancel', 'pending'])->default('awaiting_approval');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reject_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sharing_revenues');
    }
};
