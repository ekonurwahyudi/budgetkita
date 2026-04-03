<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tambak_anggotas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tambak_id')->constrained('tambaks')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('peran', ['owner', 'anggota'])->default('anggota');
            $table->timestamps();
            $table->unique(['tambak_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tambak_anggotas');
    }
};
