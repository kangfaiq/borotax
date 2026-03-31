<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel referensi daftar jalan per kelompok lokasi reklame.
     * Kelompok: A, A1, A2, A3, B, C
     */
    public function up(): void
    {
        Schema::create('kelompok_lokasi_jalan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kelompok', 10)->index()
                ->comment('Kelompok lokasi: A, A1, A2, A3, B, C');
            $table->string('nama_jalan', 255);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelompok_lokasi_jalan');
    }
};
