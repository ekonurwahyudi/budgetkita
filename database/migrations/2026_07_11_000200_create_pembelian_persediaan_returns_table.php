<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian_persediaan_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pembelian_persediaan_id')->constrained('pembelian_persediaans')->cascadeOnDelete();
            $table->foreignUuid('pembelian_persediaan_item_id')->constrained('pembelian_persediaan_items')->cascadeOnDelete();
            $table->foreignUuid('persediaan_id')->constrained('persediaans')->cascadeOnDelete();
            $table->decimal('qty', 10, 2);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('harga_total', 15, 2);
            $table->decimal('nominal_potong_hutang', 15, 2)->default(0);
            $table->decimal('nominal_refund', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_persediaan_returns');
    }
};
