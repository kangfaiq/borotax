<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom perhitungan baru ke skpd_reklame agar sesuai spesifikasi teknis.
     *
     * Formula baru:
     * POKOK DASAR = tarif_pokok × luas × muka × durasi × jumlah_reklame
     * POKOK PENYESUAIAN = POKOK DASAR × penyesuaian_lokasi × penyesuaian_produk
     * TOTAL PAJAK = POKOK PENYESUAIAN + nilai_strategis
     *
     * Kolom lama (durasi_hari, nilai_sewa_per_m2_per_hari, tarif_persen) tetap ada
     * tapi dijadikan nullable untuk backward compatibility.
     */
    public function up(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            // Kolom baru
            $table->string('kelompok_lokasi', 10)->nullable()->after('alamat_reklame')
                ->comment('Copy dari tax_object: A/A1/A2/A3/B/C');
            $table->string('bentuk', 20)->nullable()->after('kelompok_lokasi')
                ->comment('Copy dari tax_object: persegi/trapesium/lingkaran/segitiga');
            $table->string('lokasi_penempatan', 20)->nullable()->after('jumlah_muka')
                ->comment('luar_ruangan / dalam_ruangan');
            $table->string('jenis_produk', 20)->nullable()->after('lokasi_penempatan')
                ->comment('rokok / non_rokok');
            $table->unsignedInteger('jumlah_reklame')->default(1)->after('jenis_produk')
                ->comment('Jumlah unit reklame');
            $table->string('satuan_waktu', 30)->nullable()->after('jumlah_reklame')
                ->comment('perTahun/perBulan/perMinggu/perHari/perLembar/perMingguPerBuah/perHariPerBuah');
            $table->integer('durasi')->nullable()->after('satuan_waktu')
                ->comment('Jumlah satuan waktu (misal: 2 tahun, 3 bulan)');
            $table->text('tarif_pokok')->nullable()->after('durasi')
                ->comment('🔐 Tarif per m² per satuan waktu dari tabel (encrypted)');
            $table->decimal('penyesuaian_lokasi', 5, 2)->nullable()->after('tarif_pokok')
                ->comment('Multiplier: 1.00 (luar) atau 0.25 (dalam)');
            $table->decimal('penyesuaian_produk', 5, 2)->nullable()->after('penyesuaian_lokasi')
                ->comment('Multiplier: 1.00 (non-rokok) atau 1.10 (rokok)');
            $table->text('nilai_strategis')->nullable()->after('penyesuaian_produk')
                ->comment('🔐 Total nilai strategis (encrypted)');
            $table->text('pokok_pajak_dasar')->nullable()->after('nilai_strategis')
                ->comment('🔐 Pokok sebelum penyesuaian (encrypted)');
        });

        // Jadikan kolom lama nullable (backward compatibility)
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->integer('durasi_hari')->nullable()->change();
            $table->text('nilai_sewa_per_m2_per_hari')->nullable()->change();
            $table->decimal('tarif_persen', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('skpd_reklame', function (Blueprint $table) {
            $table->dropColumn([
                'kelompok_lokasi',
                'bentuk',
                'lokasi_penempatan',
                'jenis_produk',
                'jumlah_reklame',
                'satuan_waktu',
                'durasi',
                'tarif_pokok',
                'penyesuaian_lokasi',
                'penyesuaian_produk',
                'nilai_strategis',
                'pokok_pajak_dasar',
            ]);
        });
    }
};
