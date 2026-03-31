<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel tarif pokok reklame per sub jenis pajak × kelompok lokasi × satuan waktu.
     * Tarif sudah termasuk pajak 25%.
     *
     * - Reklame Tetap: tarif berbeda per kelompok lokasi (A, A1, A2, A3, B, C)
     * - Reklame Insidentil: tarif tunggal (kelompok_lokasi = null)
     */
    public function up(): void
    {
        Schema::create('reklame_tariffs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sub_jenis_pajak_id')
                ->constrained('sub_jenis_pajak')
                ->cascadeOnDelete();
            $table->string('kelompok_lokasi', 10)->nullable()
                ->comment('A/A1/A2/A3/B/C. Null untuk insidentil (tarif tunggal)');
            $table->string('satuan_waktu', 30)
                ->comment('perTahun, perBulan, perMinggu, perHari, perLembar, perMingguPerBuah, perHariPerBuah');
            $table->string('satuan_label', 30)->nullable()
                ->comment('Label display: Th/m², Bln/m², dll');
            $table->decimal('tarif_pokok', 15, 2)
                ->comment('Tarif per m² per satuan waktu (sudah termasuk 25%)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['sub_jenis_pajak_id', 'kelompok_lokasi', 'satuan_waktu'], 'reklame_tariffs_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reklame_tariffs');
    }
};
