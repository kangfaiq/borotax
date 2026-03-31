<?php

namespace Database\Seeders;

use App\Domain\Reklame\Models\ReklameNilaiStrategis;
use Illuminate\Database\Seeder;

class ReklameNilaiStrategisSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            // Kelas A (kelompok A, A1, A2, A3)
            ['kelas_kelompok' => 'A', 'luas_min' => 25.00, 'luas_max' => null,  'tarif_per_tahun' => 10000000, 'tarif_per_bulan' => 1000000],
            ['kelas_kelompok' => 'A', 'luas_min' => 10.00, 'luas_max' => 24.99, 'tarif_per_tahun' => 5000000,  'tarif_per_bulan' => 500000],

            // Kelas B (kelompok B)
            ['kelas_kelompok' => 'B', 'luas_min' => 25.00, 'luas_max' => null,  'tarif_per_tahun' => 5000000,  'tarif_per_bulan' => 500000],
            ['kelas_kelompok' => 'B', 'luas_min' => 10.00, 'luas_max' => 24.99, 'tarif_per_tahun' => 2500000,  'tarif_per_bulan' => 250000],

            // Kelas C (kelompok C)
            ['kelas_kelompok' => 'C', 'luas_min' => 25.00, 'luas_max' => null,  'tarif_per_tahun' => 3000000,  'tarif_per_bulan' => 300000],
            ['kelas_kelompok' => 'C', 'luas_min' => 10.00, 'luas_max' => 24.99, 'tarif_per_tahun' => 1500000,  'tarif_per_bulan' => 150000],
        ];

        foreach ($rates as $rate) {
            ReklameNilaiStrategis::updateOrCreate(
                [
                    'kelas_kelompok' => $rate['kelas_kelompok'],
                    'luas_min' => $rate['luas_min'],
                ],
                [
                    'luas_max' => $rate['luas_max'],
                    'tarif_per_tahun' => $rate['tarif_per_tahun'],
                    'tarif_per_bulan' => $rate['tarif_per_bulan'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('ReklameNilaiStrategisSeeder: selesai.');
    }
}
