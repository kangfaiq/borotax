<?php

namespace Database\Seeders;

use App\Domain\Tax\Models\HargaPatokanMblb;
use Illuminate\Database\Seeder;

class HargaPatokanMblbSeeder extends Seeder
{
    public function run(): void
    {
        $dasarHukum = 'Kepgub Jatim No 100.3.3.1/835/013/2025';

        $minerals = [
            ['nama_mineral' => 'Pasir Pasang', 'harga_patokan' => 100000],
            ['nama_mineral' => 'Pasir Uruk / Pasir PUK / Grosok', 'harga_patokan' => 50000],
            ['nama_mineral' => 'Pasir Cor / Sirtu / Abu Batu', 'harga_patokan' => 133500],
            ['nama_mineral' => 'Batu Gamping / Batu Kapur', 'harga_patokan' => 33500],
            ['nama_mineral' => 'Tanah Urug', 'harga_patokan' => 17000],
            ['nama_mineral' => 'Andesit / Batu Pondasi', 'harga_patokan' => 174000],
            ['nama_mineral' => 'Onyx', 'harga_patokan' => 500000],
            ['nama_mineral' => 'Batu Kumbung / Batu Putih', 'harga_patokan' => 33500],
            ['nama_mineral' => 'Pedel / Brongkol', 'harga_patokan' => 19000],
            ['nama_mineral' => 'Batu Curing', 'harga_patokan' => 30500],
            ['nama_mineral' => 'Kerikil / Koral / Kricak / Batu Pecah', 'harga_patokan' => 128000],
            ['nama_mineral' => 'Batu Gebal', 'harga_patokan' => 125000],
            ['nama_mineral' => 'Bentonite', 'harga_patokan' => 43200],
            ['nama_mineral' => 'Gips', 'harga_patokan' => 28500],
            ['nama_mineral' => 'Fosfat', 'harga_patokan' => 56500],
        ];

        foreach ($minerals as $mineral) {
            HargaPatokanMblb::updateOrCreate(
                ['nama_mineral' => $mineral['nama_mineral']],
                [
                    'sub_jenis_pajak_id' => null,
                    'harga_patokan' => (string) $mineral['harga_patokan'],
                    'satuan' => 'm3',
                    'dasar_hukum' => $dasarHukum,
                    'nama_alternatif' => [],
                    'is_active' => true,
                ]
            );
        }
    }
}
