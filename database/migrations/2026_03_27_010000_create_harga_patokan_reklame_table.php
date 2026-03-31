<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harga_patokan_reklame', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sub_jenis_pajak_id')->constrained('sub_jenis_pajak')->cascadeOnDelete();
            $table->string('kode', 100)->unique();
            $table->string('nama', 255);
            $table->string('nama_lengkap', 255)->nullable();
            $table->boolean('is_insidentil')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_patokan_reklame');
    }
};