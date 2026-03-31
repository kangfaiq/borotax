<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel nilai strategis reklame.
     * Hanya berlaku untuk Reklame Tetap dengan luas ≥ 10 m².
     *
     * Kelas kelompok:
     * - A = kelompok A, A1, A2, A3
     * - B = kelompok B
     * - C = kelompok C
     */
    public function up(): void
    {
        Schema::create('reklame_nilai_strategis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kelas_kelompok', 5)
                ->comment('A, B, atau C');
            $table->decimal('luas_min', 10, 2)
                ->comment('Batas bawah luas (m²)');
            $table->decimal('luas_max', 10, 2)->nullable()
                ->comment('Batas atas luas (m²). Null = tak terbatas');
            $table->decimal('tarif_per_tahun', 15, 2)
                ->comment('Nilai strategis per tahun');
            $table->decimal('tarif_per_bulan', 15, 2)
                ->comment('Nilai strategis per bulan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reklame_nilai_strategis');
    }
};
