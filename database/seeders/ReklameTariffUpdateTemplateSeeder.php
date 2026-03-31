<?php

namespace Database\Seeders;

use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\ReklameTariff;
use Illuminate\Database\Seeder;

/**
 * Template seeder untuk update tarif reklame.
 *
 * Cara pakai:
 * 1. Copy file ini, rename sesuai periode (misal ReklameTariffUpdate2026Q3Seeder)
 * 2. Ubah $berlakuMulai ke tanggal berlaku tarif baru
 * 3. Isi array $perubahanTarif hanya dengan tarif yang berubah
 * 4. Jalankan: php artisan db:seed --class=ReklameTariffUpdateTemplateSeeder
 *
 * Seeder ini otomatis:
 * - Menutup tarif lama (set berlaku_sampai = sehari sebelum tanggal baru)
 * - Insert tarif baru dengan berlaku_mulai yang ditentukan
 * - Tarif lama tetap tersimpan sebagai riwayat
 */
class ReklameTariffUpdateTemplateSeeder extends Seeder
{
    /**
     * Tanggal mulai berlaku tarif baru (format: Y-m-d).
     */
    private string $berlakuMulai = '2026-07-01';

    public function run(): void
    {
        // ═══════════════════════════════════════════════════════════
        // Isi dengan tarif yang BERUBAH saja.
        // Kelompok yang tidak dicantumkan = tidak berubah.
        // ═══════════════════════════════════════════════════════════

        $perubahanTetap = [
            // Contoh: LED/Videotron naik tarif per tahun untuk kelompok A & A1
            // ['kode' => 'RKL_LED_VIDEOTRON', 'satuan' => 'perTahun', 'label' => 'Th/m²',
            //  'tarif' => [
            //      'A'  => 1600000, // naik dari 1500000
            //      'A1' => 1550000, // naik dari 1455000
            //  ]],
        ];

        $perubahanInsidentil = [
            // Contoh: Spanduk naik tarif per bulan
            // ['kode' => 'RKL_SPANDUK', 'satuan' => 'perBulan', 'label' => 'Bln/m²', 'tarif' => 25000],
        ];

        if (empty($perubahanTetap) && empty($perubahanInsidentil)) {
            $this->command->warn('Tidak ada perubahan tarif yang dikonfigurasi. Silakan isi array $perubahanTetap / $perubahanInsidentil.');
            return;
        }

        $tanggalTutup = date('Y-m-d', strtotime($this->berlakuMulai . ' -1 day'));
        $totalUpdated = 0;

        // ── Proses Reklame Tetap ────────────────────────────────
        foreach ($perubahanTetap as $entry) {
            $subId = SubJenisPajak::where('kode', $entry['kode'])->value('id');
            if (!$subId) {
                $this->command->warn("SubJenisPajak {$entry['kode']} tidak ditemukan. Skip.");
                continue;
            }

            foreach ($entry['tarif'] as $kelompok => $tarifBaru) {
                // 1. Tutup tarif lama (set berlaku_sampai)
                $closed = ReklameTariff::where('sub_jenis_pajak_id', $subId)
                    ->where('kelompok_lokasi', $kelompok)
                    ->where('satuan_waktu', $entry['satuan'])
                    ->where('is_active', true)
                    ->whereNull('berlaku_sampai')
                    ->update(['berlaku_sampai' => $tanggalTutup]);

                // 2. Insert tarif baru
                ReklameTariff::create([
                    'sub_jenis_pajak_id' => $subId,
                    'kelompok_lokasi' => $kelompok,
                    'satuan_waktu' => $entry['satuan'],
                    'satuan_label' => $entry['label'],
                    'tarif_pokok' => $tarifBaru,
                    'is_active' => true,
                    'berlaku_mulai' => $this->berlakuMulai,
                    'berlaku_sampai' => null,
                ]);

                $totalUpdated++;
                $this->command->info("[TETAP] {$entry['kode']} {$kelompok} {$entry['satuan']}: Rp " . number_format($tarifBaru, 0, ',', '.') . " (berlaku {$this->berlakuMulai})");
            }
        }

        // ── Proses Reklame Insidentil ───────────────────────────
        foreach ($perubahanInsidentil as $entry) {
            $subId = SubJenisPajak::where('kode', $entry['kode'])->value('id');
            if (!$subId) {
                $this->command->warn("SubJenisPajak {$entry['kode']} tidak ditemukan. Skip.");
                continue;
            }

            // 1. Tutup tarif lama
            ReklameTariff::where('sub_jenis_pajak_id', $subId)
                ->whereNull('kelompok_lokasi')
                ->where('satuan_waktu', $entry['satuan'])
                ->where('is_active', true)
                ->whereNull('berlaku_sampai')
                ->update(['berlaku_sampai' => $tanggalTutup]);

            // 2. Insert tarif baru
            ReklameTariff::create([
                'sub_jenis_pajak_id' => $subId,
                'kelompok_lokasi' => null,
                'satuan_waktu' => $entry['satuan'],
                'satuan_label' => $entry['label'],
                'tarif_pokok' => $entry['tarif'],
                'is_active' => true,
                'berlaku_mulai' => $this->berlakuMulai,
                'berlaku_sampai' => null,
            ]);

            $totalUpdated++;
            $this->command->info("[INSIDENTIL] {$entry['kode']} {$entry['satuan']}: Rp " . number_format($entry['tarif'], 0, ',', '.') . " (berlaku {$this->berlakuMulai})");
        }

        $this->command->info("Update tarif selesai. {$totalUpdated} tarif diperbarui, berlaku mulai {$this->berlakuMulai}.");
    }
}
