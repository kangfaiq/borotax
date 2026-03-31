<?php

namespace Database\Seeders;

use App\Domain\Tax\Models\HargaPatokanSarangWalet;
use Illuminate\Database\Seeder;

class HargaPatokanSarangWaletSeeder extends Seeder
{
    public function run(): void
    {
        $dasarHukum = 'Perda Kab. Bojonegoro No 8 Tahun 2025';

        $jenisSarang = [
            ['nama_jenis' => 'Mangkuk', 'harga_patokan' => 6000000],
            ['nama_jenis' => 'Sudut', 'harga_patokan' => 5500000],
            ['nama_jenis' => 'Patahan', 'harga_patokan' => 5000000],
            ['nama_jenis' => 'Bubuk', 'harga_patokan' => 4500000],
        ];

        foreach ($jenisSarang as $item) {
            HargaPatokanSarangWalet::updateOrCreate(
                ['nama_jenis' => $item['nama_jenis']],
                [
                    'harga_patokan' => (string) $item['harga_patokan'],
                    'satuan' => 'kg',
                    'dasar_hukum' => $dasarHukum,
                    'is_active' => true,
                ]
            );
        }
    }
}
