<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Konsolidasi objek pajak: menambahkan kolom dari reklame_objects & water_objects
     * ke tabel tax_objects. Tabel lama (reklame_objects, water_objects) tetap ada
     * untuk sementara karena masih direferensikan oleh tabel SKPD.
     */
    public function up(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            // Kelompok lokasi (untuk Reklame): A, A1, A2, A3, B, C
            $table->string('kelompok_lokasi', 10)->nullable()->after('foto_objek_path')
                ->comment('Kelompok lokasi reklame (A, A1, A2, A3, B, C)');

            // Kelompok pemakaian (untuk Air Tanah): 1, 2, 3, 4, 5
            $table->string('kelompok_pemakaian', 5)->nullable()->after('kelompok_lokasi')
                ->comment('Kelompok pemakaian air tanah (1-5)');

            // Bobot SDA (untuk Air Tanah): A, B, C, D
            $table->string('bobot_sda', 5)->nullable()->after('kelompok_pemakaian')
                ->comment('Bobot SDA air tanah (A, B, C, D)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->dropColumn(['kelompok_lokasi', 'kelompok_pemakaian', 'bobot_sda']);
        });
    }
};
