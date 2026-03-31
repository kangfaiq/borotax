<?php

namespace Database\Seeders;

use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use Illuminate\Database\Seeder;

/**
 * Seeder khusus untuk menambahkan sub jenis pajak reklame baru
 * dan menonaktifkan sub jenis lama.
 *
 * Sub jenis baru sesuai kalkulator pajak reklame:
 * - 10 jenis Reklame Tetap (kategori = 'tetap')
 * - 10 jenis Reklame Insidentil (kategori = 'insidentil')
 */
class ReklameSubJenisPajakSeeder extends Seeder
{
    public function run(): void
    {
        $subJenisMap = SubJenisPajak::whereIn('kode', ['REKLAME_TETAP', 'REKLAME_KAIN'])
            ->pluck('id', 'kode');

        if (!isset($subJenisMap['REKLAME_TETAP'], $subJenisMap['REKLAME_KAIN'])) {
            $this->command->warn('SubJenisPajak reklame utama belum tersedia. Skip seeder.');
            return;
        }

        SubJenisPajak::where('kode', 'like', 'RKL_%')->update(['is_active' => false]);

        $tetap = [
            ['kode' => 'RKL_LED_VIDEOTRON',     'nama' => 'LED / Videotron',                            'urutan' => 1],
            ['kode' => 'RKL_MEGATRON',           'nama' => 'Megatron',                                   'urutan' => 2],
            ['kode' => 'RKL_ELEKTRONIK_DIGITAL', 'nama' => 'Elektronik / Digital',                       'urutan' => 3],
            ['kode' => 'RKL_NEON_BOX',           'nama' => 'Neon Box',                                   'urutan' => 4],
            ['kode' => 'RKL_BILLBOARD_GTE_10',   'nama' => 'Billboard / Papan Nama / Tinplat (≥10m²)',   'urutan' => 5],
            ['kode' => 'RKL_BILLBOARD_LT_10',    'nama' => 'Billboard / Papan Nama / Tinplat (<10m²)',   'urutan' => 6],
            ['kode' => 'RKL_ROMBONG',            'nama' => 'Rombong',                                    'urutan' => 7],
            ['kode' => 'RKL_KENDARAAN',          'nama' => 'Kendaraan / Berjalan',                       'urutan' => 8],
            ['kode' => 'RKL_PERAGAAN_TETAP',     'nama' => 'Peragaan',                                   'urutan' => 9],
            ['kode' => 'RKL_GRAFFITI',           'nama' => 'Graffiti',                                   'urutan' => 10],
        ];

        foreach ($tetap as $item) {
            HargaPatokanReklame::updateOrCreate(
                ['kode' => $item['kode']],
                [
                    'sub_jenis_pajak_id' => $subJenisMap['REKLAME_TETAP'],
                    'nama' => $item['nama'],
                    'is_insidentil' => false,
                    'is_active' => true,
                    'urutan' => $item['urutan'],
                ]
            );
        }

        $insidentil = [
            ['kode' => 'RKL_SPANDUK',              'nama' => 'Spanduk / Umbul-umbul / Banner / Flagchain / Apung', 'urutan' => 11],
            ['kode' => 'RKL_SELEBARAN',             'nama' => 'Selebaran',                                          'urutan' => 12],
            ['kode' => 'RKL_STIKER',                'nama' => 'Stiker',                                             'urutan' => 13],
            ['kode' => 'RKL_BERJALAN_INSIDENTIL',   'nama' => 'Reklame Berjalan dan Sejenisnya',                     'urutan' => 14],
            ['kode' => 'RKL_UDARA',                 'nama' => 'Udara',                                               'urutan' => 15],
            ['kode' => 'RKL_BALIHO',                'nama' => 'Baliho',                                              'urutan' => 16],
            ['kode' => 'RKL_PERAGAAN_INSIDENTIL',   'nama' => 'Peragaan (Insidentil)',                                'urutan' => 17],
            ['kode' => 'RKL_BALON_BONEKA',           'nama' => 'Balon Boneka',                                        'urutan' => 18],
            ['kode' => 'RKL_BALON_GAPURA',           'nama' => 'Balon Gapura',                                        'urutan' => 19],
            ['kode' => 'RKL_TENDA',                  'nama' => 'Tenda',                                               'urutan' => 20],
        ];

        foreach ($insidentil as $item) {
            HargaPatokanReklame::updateOrCreate(
                ['kode' => $item['kode']],
                [
                    'sub_jenis_pajak_id' => $subJenisMap['REKLAME_KAIN'],
                    'nama' => $item['nama'],
                    'is_insidentil' => true,
                    'is_active' => true,
                    'urutan' => $item['urutan'],
                ]
            );
        }

        $this->command->info('ReklameSubJenisPajakSeeder: master harga patokan reklame diperbarui.');
    }
}
