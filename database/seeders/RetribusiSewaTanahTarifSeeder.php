<?php

namespace Database\Seeders;

use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Retribusi\Models\TarifSewaTanah;
use Illuminate\Database\Seeder;

class RetribusiSewaTanahTarifSeeder extends Seeder
{
    public function run(): void
    {
        $tarifs = [
            ['kode_sub' => 'SEWA_TANAH_PERMANEN', 'tarif_nominal' => 80000, 'satuan_waktu' => 'perTahun'],
            ['kode_sub' => 'SEWA_TANAH_KAIN', 'tarif_nominal' => 20000, 'satuan_waktu' => 'perBulan'],
            ['kode_sub' => 'SEWA_TANAH_RUMIJA', 'tarif_nominal' => 80000, 'satuan_waktu' => 'perTahun'],
        ];

        foreach ($tarifs as $data) {
            $subJenisPajak = SubJenisPajak::where('kode', $data['kode_sub'])->first();
            if (! $subJenisPajak) {
                continue;
            }

            TarifSewaTanah::updateOrCreate(
                ['sub_jenis_pajak_id' => $subJenisPajak->id],
                [
                    'tarif_nominal' => $data['tarif_nominal'],
                    'satuan_waktu' => $data['satuan_waktu'],
                    'berlaku_mulai' => '2026-01-01',
                ],
            );
        }
    }
}
