<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\AirTanah\Models\NpaAirTanah;

class NpaAirTanahProgresifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data lama
        NpaAirTanah::truncate();

        $kriteriaList = [
            'Air Tanah Kualitas Baik, Ada Sumber Alternatif',
            'Air Tanah Kualitas Baik, Tidak Ada Sumber Alternatif',
            'Air Tanah Kualitas Tidak Baik, Ada Sumber Alternatif',
            'Air Tanah Kualitas Tidak Baik, Tidak Ada Sumber Alternatif',
        ];

        // =========================================================================
        // DATA ZONA 3 (Berlaku s/d Juni 2026) -> Harga dipukul rata di semua kriteria
        // =========================================================================
        $zona3 = [
            'Kelompok 1' => [4300, 5600, 7500, 10400, 14700],
            'Kelompok 2' => [3700, 4700, 6200, 8400, 11800],
            'Kelompok 3' => [3100, 3850, 4900, 6500, 8900],
            'Kelompok 4' => [2600, 3000, 3600, 4600, 6000],
            'Kelompok 5' => [2000, 2100, 2350, 2700, 3100],
        ];

        foreach ($zona3 as $kel => $prices) {
            foreach ($kriteriaList as $kriteria) {
                NpaAirTanah::create([
                    'kelompok_pemakaian' => $kel,
                    'kriteria_sda'       => $kriteria,
                    'is_active'          => true,
                    'berlaku_mulai'      => '2022-01-01', // Asumsi Pergub 2022
                    'berlaku_sampai'     => '2026-06-30',
                    'dasar_hukum'        => 'Pergub Jatim Zona 3 Lama',
                    'npa_tiers'          => [
                        ['min_vol' => 0,    'max_vol' => 50,   'npa' => $prices[0]],
                        ['min_vol' => 51,   'max_vol' => 500,  'npa' => $prices[1]],
                        ['min_vol' => 501,  'max_vol' => 1000, 'npa' => $prices[2]],
                        ['min_vol' => 1001, 'max_vol' => 2500, 'npa' => $prices[3]],
                        ['min_vol' => 2501, 'max_vol' => null, 'npa' => $prices[4]],
                    ]
                ]);
            }
        }

        // =========================================================================
        // DATA ZONA 2 (Berlaku Juli 2026 dst) -> Beda tiap kriteria
        // =========================================================================

        // a. Baik, Ada Sumber
        $zona2_BaikAda = [
            'Kelompok 1' => [29674, 33720, 39790, 48894, 62539],
            'Kelompok 2' => [27875, 31022, 35743, 42824, 53457],
            'Kelompok 3' => [26077, 28325, 31697, 36755, 44331],
            'Kelompok 4' => [24278, 25627, 27650, 30685, 35249],
            'Kelompok 5' => [22480, 22930, 23604, 24616, 26122],
        ];
        // b. Baik, Tidak Ada Sumber
        $zona2_BaikTidak = [
            'Kelompok 1' => [20232, 24278, 30348, 39452, 53098],
            'Kelompok 2' => [18434, 21581, 26302, 33383, 44016],
            'Kelompok 3' => [16635, 18883, 22255, 27313, 34889],
            'Kelompok 4' => [14837, 16186, 18209, 21244, 25807],
            'Kelompok 5' => [13038, 13488, 14162, 15174, 16680],
        ];
        // c. Tidak Baik, Ada Sumber
        $zona2_TidakBaikAda = [
            'Kelompok 1' => [13488, 17534, 23604, 32708, 46354],
            'Kelompok 2' => [11690, 14837, 19558, 26639, 37272],
            'Kelompok 3' => [ 9891, 12139, 15511, 20569, 28145],
            'Kelompok 4' => [ 8093,  9442, 11465, 14500, 19063],
            'Kelompok 5' => [ 6294,  6744,  7418,  8430,  9936],
        ];
        // d. Tidak Baik, Tidak Ada
        $zona2_TidakBaikTidak = [
            'Kelompok 1' => [9442, 13488, 19558, 28662, 42307],
            'Kelompok 2' => [7643, 10790, 15511, 22592, 33225],
            'Kelompok 3' => [5845,  8093, 11465, 16523, 24099],
            'Kelompok 4' => [4046,  5395,  7418, 10453, 15017],
            'Kelompok 5' => [2248,  2698,  3372,  4384,  5890],
        ];

        $zona2Map = [
            'Air Tanah Kualitas Baik, Ada Sumber Alternatif' => $zona2_BaikAda,
            'Air Tanah Kualitas Baik, Tidak Ada Sumber Alternatif' => $zona2_BaikTidak,
            'Air Tanah Kualitas Tidak Baik, Ada Sumber Alternatif' => $zona2_TidakBaikAda,
            'Air Tanah Kualitas Tidak Baik, Tidak Ada Sumber Alternatif' => $zona2_TidakBaikTidak,
        ];

        foreach ($zona2Map as $kriteria => $groups) {
            foreach ($groups as $kel => $prices) {
                NpaAirTanah::create([
                    'kelompok_pemakaian' => $kel,
                    'kriteria_sda'       => $kriteria,
                    'is_active'          => true,
                    'berlaku_mulai'      => '2026-07-01', // Mulai Juli 2026
                    'berlaku_sampai'     => null, // Sampai selamanya
                    'dasar_hukum'        => 'Pergub Baru Zona 2',
                    'npa_tiers'          => [
                        ['min_vol' => 0,    'max_vol' => 50,   'npa' => $prices[0]],
                        ['min_vol' => 51,   'max_vol' => 500,  'npa' => $prices[1]],
                        ['min_vol' => 501,  'max_vol' => 1000, 'npa' => $prices[2]],
                        ['min_vol' => 1001, 'max_vol' => 2500, 'npa' => $prices[3]],
                        ['min_vol' => 2501, 'max_vol' => null, 'npa' => $prices[4]],
                    ]
                ]);
            }
        }
    }
}
