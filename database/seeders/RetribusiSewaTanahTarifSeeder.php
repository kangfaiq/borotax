<?php

namespace Database\Seeders;

use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Retribusi\Models\TarifSewaTanah;
use Illuminate\Database\Seeder;

class RetribusiSewaTanahTarifSeeder extends Seeder
{
    public function run(): void
    {
        $tarifGroups = [
            [
                'kode_reklame' => 'REKLAME_TETAP',
                'tarif_nominal' => 80000,
                'satuan_waktu' => 'perTahun',
                'kode_sub' => ['SEWA_TANAH_PERMANEN', 'SEWA_TANAH_RUMIJA'],
            ],
            [
                'kode_reklame' => 'REKLAME_KAIN',
                'tarif_nominal' => 20000,
                'satuan_waktu' => 'perBulan',
                'kode_sub' => ['SEWA_TANAH_KAIN'],
            ],
        ];

        foreach ($tarifGroups as $group) {
            foreach ($group['kode_sub'] as $kodeSub) {
                $subJenisPajak = SubJenisPajak::where('kode', $kodeSub)->first();

                if (! $subJenisPajak) {
                    continue;
                }

                TarifSewaTanah::updateOrCreate(
                    ['sub_jenis_pajak_id' => $subJenisPajak->id],
                    [
                        'tarif_nominal' => $group['tarif_nominal'],
                        'satuan_waktu' => $group['satuan_waktu'],
                        'berlaku_mulai' => '2026-01-01',
                    ],
                );
            }
        }
    }
}
