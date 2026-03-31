<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom bentuk reklame dan dimensi tambahan ke tax_objects.
     *
     * Bentuk:
     * - persegi: panjang × lebar (kolom sudah ada)
     * - trapesium: ((sisi_atas + sisi_bawah) / 2) × tinggi
     * - lingkaran: π × (diameter / 2)²
     * - segitiga: (alas × tinggi) / 2
     */
    public function up(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->string('bentuk', 20)->nullable()->after('kelompok_lokasi')
                ->comment('persegi, trapesium, lingkaran, segitiga');
            $table->decimal('tinggi', 8, 2)->nullable()->after('lebar')
                ->comment('Untuk trapesium & segitiga (meter)');
            $table->decimal('sisi_atas', 8, 2)->nullable()->after('tinggi')
                ->comment('Untuk trapesium (meter)');
            $table->decimal('sisi_bawah', 8, 2)->nullable()->after('sisi_atas')
                ->comment('Untuk trapesium (meter)');
            $table->decimal('diameter', 8, 2)->nullable()->after('sisi_bawah')
                ->comment('Untuk lingkaran (meter)');
            $table->decimal('alas', 8, 2)->nullable()->after('diameter')
                ->comment('Untuk segitiga (meter)');
        });
    }

    public function down(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->dropColumn([
                'bentuk',
                'tinggi',
                'sisi_atas',
                'sisi_bawah',
                'diameter',
                'alas',
            ]);
        });
    }
};
