<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Menambahkan kolom jatuh_tempo (tanggal jatuh tempo / batas penyetoran terakhir)
     * ke tabel skpd_reklame dan skpd_air_tanah.
     *
     * Logika perhitungan jatuh tempo:
     * - Reklame: masa_berlaku_mulai + 1 bulan - 1 hari
     * - Air Tanah: akhir bulan berikutnya dari periode bulan
     *
     * Konsisten dengan kalkulator sanksi di web dan mobile.
     */
    public function up(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->date('jatuh_tempo')->nullable()->after('durasi_hari')
                ->comment('Tanggal jatuh tempo / batas penyetoran terakhir');
        });

        Schema::table('skpd_air_tanah', function (Blueprint $table) {
            $table->date('jatuh_tempo')->nullable()->after('periode_bulan')
                ->comment('Tanggal jatuh tempo / batas penyetoran terakhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->dropColumn('jatuh_tempo');
        });

        Schema::table('skpd_air_tanah', function (Blueprint $table) {
            $table->dropColumn('jatuh_tempo');
        });
    }
};
