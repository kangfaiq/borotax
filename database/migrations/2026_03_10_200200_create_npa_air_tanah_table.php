<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('npa_air_tanah', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kelompok_pemakaian', 100)->comment('Kelompok pemakaian air (Pasal 6 Pergub 35/2025)');
            $table->string('kriteria_sda', 100)->comment('Kriteria SDA (Pasal 5 ayat 2 huruf a Pergub 35/2025)');
            $table->text('npa_per_m3')->comment('Nilai Perolehan Air per m3 (encrypted)');
            $table->date('berlaku_mulai')->comment('Tanggal mulai berlaku');
            $table->date('berlaku_sampai')->nullable()->comment('Tanggal akhir berlaku (null = masih berlaku)');
            $table->string('dasar_hukum', 255)->nullable()->comment('Referensi peraturan');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['kelompok_pemakaian', 'kriteria_sda', 'is_active'], 'npa_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('npa_air_tanah');
    }
};
