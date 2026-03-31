<?php

namespace Database\Seeders;

use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\TarifPajak;
use Illuminate\Database\Seeder;

/**
 * Seed tarif_pajak dari data sub_jenis_pajak.tarif_persen yang sudah ada.
 *
 * Setiap sub jenis pajak aktif akan mendapat record tarif_pajak
 * dengan berlaku_mulai = 2024-01-01 (asumsi tanggal awal sistem).
 */
class TarifPajakSeeder extends Seeder
{
    public function run(): void
    {
        $subJenisList = SubJenisPajak::where('is_active', true)->get();

        foreach ($subJenisList as $sub) {
            TarifPajak::updateOrCreate(
                [
                    'sub_jenis_pajak_id' => $sub->id,
                    'berlaku_mulai' => '2024-01-01',
                ],
                [
                    'tarif_persen' => $sub->tarif_persen,
                    'berlaku_sampai' => null,
                    'dasar_hukum' => $sub->dasar_hukum ?? 'Perda Kab. Bojonegoro',
                    'is_active' => true,
                    'keterangan' => "Tarif awal dari sub jenis: {$sub->kode}",
                ]
            );
        }

        $this->command->info("Seeded " . $subJenisList->count() . " tarif pajak records.");
    }
}
