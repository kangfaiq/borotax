<?php

namespace Database\Seeders;

use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Reklame\Models\ReklameTariff;
use Illuminate\Database\Seeder;

class ReklameTariffSeeder extends Seeder
{
    public function run(): void
    {
        if (!HargaPatokanReklame::query()->exists()) {
            $this->command->warn('Master harga patokan reklame belum tersedia. Skip seeder.');
            return;
        }

        $getHargaPatokanId = fn (string $kode) => HargaPatokanReklame::where('kode', $kode)->value('id');

        // ═══════════════════════════════════════════════
        // REKLAME TETAP — tarif per kelompok lokasi
        // Pajak = (NSPR + NJOPR) × 25%
        // NJOPR sama untuk semua kelompok per jenis/satuan
        // ═══════════════════════════════════════════════

        $tetapTarifs = [
            // LED / Videotron
            ['kode' => 'RKL_LED_VIDEOTRON', 'satuan' => 'perTahun', 'label' => 'Th/m²', 'njopr' => 4500000,
             'nspr' => ['A' => 1500000, 'A1' => 1320000, 'A2' => 1140000, 'A3' => 900000, 'B' => 720000, 'C' => 600000]],
            ['kode' => 'RKL_LED_VIDEOTRON', 'satuan' => 'perBulan', 'label' => 'Bln/m²', 'njopr' => 450000,
             'nspr' => ['A' => 150000, 'A1' => 132000, 'A2' => 114000, 'A3' => 90000, 'B' => 72000, 'C' => 60000]],

            // Megatron
            ['kode' => 'RKL_MEGATRON', 'satuan' => 'perTahun', 'label' => 'Th/m²', 'njopr' => 750000,
             'nspr' => ['A' => 750000, 'A1' => 700000, 'A2' => 650000, 'A3' => 600000, 'B' => 550000, 'C' => 500000]],
            ['kode' => 'RKL_MEGATRON', 'satuan' => 'perBulan', 'label' => 'Bln/m²', 'njopr' => 75000,
             'nspr' => ['A' => 75000, 'A1' => 70000, 'A2' => 65000, 'A3' => 60000, 'B' => 55000, 'C' => 50000]],

            // Elektronik / Digital
            ['kode' => 'RKL_ELEKTRONIK_DIGITAL', 'satuan' => 'perTahun', 'label' => 'Th/m²', 'njopr' => 650000,
             'nspr' => ['A' => 650000, 'A1' => 625000, 'A2' => 600000, 'A3' => 575000, 'B' => 550000, 'C' => 525000]],
            ['kode' => 'RKL_ELEKTRONIK_DIGITAL', 'satuan' => 'perBulan', 'label' => 'Bln/m²', 'njopr' => 65000,
             'nspr' => ['A' => 65000, 'A1' => 62500, 'A2' => 60000, 'A3' => 57500, 'B' => 55000, 'C' => 52500]],

            // Neon Box
            ['kode' => 'RKL_NEON_BOX', 'satuan' => 'perTahun', 'label' => 'Th/m²', 'njopr' => 480000,
             'nspr' => ['A' => 625000, 'A1' => 595000, 'A2' => 565000, 'A3' => 505000, 'B' => 475000, 'C' => 445000]],

            // Billboard / Papan Nama / Tinplat ≥ 10m²
            ['kode' => 'RKL_BILLBOARD_GTE_10', 'satuan' => 'perTahun', 'label' => 'Th/m²', 'njopr' => 450000,
             'nspr' => ['A' => 500000, 'A1' => 440000, 'A2' => 380000, 'A3' => 320000, 'B' => 260000, 'C' => 230000]],
            ['kode' => 'RKL_BILLBOARD_GTE_10', 'satuan' => 'perBulan', 'label' => 'Bln/m²', 'njopr' => 45000,
             'nspr' => ['A' => 50000, 'A1' => 44000, 'A2' => 38000, 'A3' => 32000, 'B' => 26000, 'C' => 23000]],

            // Billboard / Papan Nama / Tinplat < 10m²
            ['kode' => 'RKL_BILLBOARD_LT_10', 'satuan' => 'perTahun', 'label' => 'Th/m²', 'njopr' => 300000,
             'nspr' => ['A' => 500000, 'A1' => 470000, 'A2' => 440000, 'A3' => 410000, 'B' => 380000, 'C' => 320000]],
            ['kode' => 'RKL_BILLBOARD_LT_10', 'satuan' => 'perBulan', 'label' => 'Bln/m²', 'njopr' => 30000,
             'nspr' => ['A' => 50000, 'A1' => 47000, 'A2' => 44000, 'A3' => 41000, 'B' => 38000, 'C' => 32000]],

            // Rombong
            ['kode' => 'RKL_ROMBONG', 'satuan' => 'perTahun', 'label' => 'Th/m²', 'njopr' => 360000,
             'nspr' => ['A' => 500000, 'A1' => 440000, 'A2' => 380000, 'A3' => 350000, 'B' => 320000, 'C' => 260000]],

            // Kendaraan / Berjalan
            ['kode' => 'RKL_KENDARAAN', 'satuan' => 'perTahun', 'label' => 'Th/m²', 'njopr' => 240000,
             'nspr' => ['A' => 250000, 'A1' => 250000, 'A2' => 250000, 'A3' => 250000, 'B' => 250000, 'C' => 250000]],

            // Peragaan Tetap
            ['kode' => 'RKL_PERAGAAN_TETAP', 'satuan' => 'perTahun', 'label' => 'Th/m²', 'njopr' => 360000,
             'nspr' => ['A' => 200000, 'A1' => 140000, 'A2' => 80000, 'A3' => 50000, 'B' => 20000, 'C' => -40000]],

            // Graffiti
            ['kode' => 'RKL_GRAFFITI', 'satuan' => 'perTahun', 'label' => 'Th/m²', 'njopr' => 210000,
             'nspr' => ['A' => 250000, 'A1' => 208000, 'A2' => 174000, 'A3' => 147000, 'B' => 126000, 'C' => 109000]],
        ];

        foreach ($tetapTarifs as $entry) {
            $hargaPatokanId = $getHargaPatokanId($entry['kode']);
            if (!$hargaPatokanId) {
                $this->command->warn("HargaPatokanReklame {$entry['kode']} tidak ditemukan. Skip.");
                continue;
            }

            $njopr = $entry['njopr'];
            foreach ($entry['nspr'] as $kelompok => $nspr) {
                $tarifPokok = ($nspr + $njopr) * 0.25;
                ReklameTariff::updateOrCreate(
                    [
                        'harga_patokan_reklame_id' => $hargaPatokanId,
                        'kelompok_lokasi' => $kelompok,
                        'satuan_waktu' => $entry['satuan'],
                        'berlaku_mulai' => '2026-01-01',
                    ],
                    [
                        'satuan_label' => $entry['label'],
                        'nspr' => $nspr,
                        'njopr' => $njopr,
                        'tarif_pokok' => $tarifPokok,
                        'is_active' => true,
                    ]
                );
            }
        }

        // ═══════════════════════════════════════════════
        // REKLAME INSIDENTIL — tarif tunggal
        // Pajak = (NSPR + NJOPR) × 25%
        // ═══════════════════════════════════════════════

        $insidentilTarifs = [
            // Spanduk/umbul-umbul/banner/flagchain/apung
            ['kode' => 'RKL_SPANDUK', 'satuan' => 'perBulan', 'label' => 'Bln/m²', 'nspr' => 50000, 'njopr' => 30000],
            ['kode' => 'RKL_SPANDUK', 'satuan' => 'perMinggu', 'label' => 'Minggu/m²', 'nspr' => 45000, 'njopr' => 0],
            ['kode' => 'RKL_SPANDUK', 'satuan' => 'perHari', 'label' => 'Hari/m²', 'nspr' => 10000, 'njopr' => 0],

            // Selebaran
            ['kode' => 'RKL_SELEBARAN', 'satuan' => 'perLembar', 'label' => 'Lembar/kertas', 'nspr' => 1500, 'njopr' => 1000],

            // Stiker
            ['kode' => 'RKL_STIKER', 'satuan' => 'perLembar', 'label' => 'Lembar/kertas', 'nspr' => 2000, 'njopr' => 4500],

            // Reklame Berjalan Insidentil dan sejenisnya
            ['kode' => 'RKL_BERJALAN_INSIDENTIL', 'satuan' => 'perHari', 'label' => 'Hari/m²', 'nspr' => 20000, 'njopr' => 20000],

            // Udara
            ['kode' => 'RKL_UDARA', 'satuan' => 'perMingguPerBuah', 'label' => 'Minggu/buah', 'nspr' => 750000, 'njopr' => 400000],

            // Baliho
            ['kode' => 'RKL_BALIHO', 'satuan' => 'perBulan', 'label' => 'Bln/m²', 'nspr' => 100000, 'njopr' => 375000],
            ['kode' => 'RKL_BALIHO', 'satuan' => 'perMinggu', 'label' => 'Minggu/m²', 'nspr' => 0, 'njopr' => 0],
            ['kode' => 'RKL_BALIHO', 'satuan' => 'perHari', 'label' => 'Hari/m²', 'nspr' => 0, 'njopr' => 0],

            // Peragaan Insidentil
            ['kode' => 'RKL_PERAGAAN_INSIDENTIL', 'satuan' => 'perMinggu', 'label' => 'Minggu/m²', 'nspr' => 100000, 'njopr' => 200000],
            ['kode' => 'RKL_PERAGAAN_INSIDENTIL', 'satuan' => 'perHari', 'label' => 'Hari/m²', 'nspr' => 0, 'njopr' => 0],

            // Balon Boneka
            ['kode' => 'RKL_BALON_BONEKA', 'satuan' => 'perHariPerBuah', 'label' => 'Hari/buah', 'nspr' => 100000, 'njopr' => 60000],

            // Balon Gapura
            ['kode' => 'RKL_BALON_GAPURA', 'satuan' => 'perHariPerBuah', 'label' => 'Hari/buah', 'nspr' => 100000, 'njopr' => 150000],

            // Tenda
            ['kode' => 'RKL_TENDA', 'satuan' => 'perMingguPerBuah', 'label' => 'Minggu/buah', 'nspr' => 125000, 'njopr' => 450000],
            ['kode' => 'RKL_TENDA', 'satuan' => 'perHariPerBuah', 'label' => 'Hari/buah', 'nspr' => 0, 'njopr' => 0],
        ];

        foreach ($insidentilTarifs as $entry) {
            $hargaPatokanId = $getHargaPatokanId($entry['kode']);
            if (!$hargaPatokanId) {
                $this->command->warn("HargaPatokanReklame {$entry['kode']} tidak ditemukan. Skip.");
                continue;
            }

            $nspr = $entry['nspr'];
            $njopr = $entry['njopr'];
            $tarifPokok = ($nspr + $njopr) * 0.25;

            ReklameTariff::updateOrCreate(
                [
                    'harga_patokan_reklame_id' => $hargaPatokanId,
                    'kelompok_lokasi' => null,
                    'satuan_waktu' => $entry['satuan'],
                    'berlaku_mulai' => '2026-01-01',
                ],
                [
                    'satuan_label' => $entry['label'],
                    'nspr' => $nspr,
                    'njopr' => $njopr,
                    'tarif_pokok' => $tarifPokok,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('ReklameTariffSeeder: selesai.');
    }
}
