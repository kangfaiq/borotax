<?php

namespace Database\Seeders;

use App\Domain\Master\Models\JenisPajak;
use Illuminate\Database\Seeder;

class JenisPajakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisPajak = [
            [
                'kode' => '41101',
                'nama' => 'PBJT atas Jasa Perhotelan',
                'nama_singkat' => 'Hotel',
                'deskripsi' => 'Pajak atas pelayanan yang disediakan oleh hotel',
                'icon' => '🏨',
                'tarif_default' => 10.00,
                'tipe_assessment' => 'self_assessment',
                'is_active' => true,
                'urutan' => 1,
            ],
            [
                'kode' => '41102',
                'nama' => 'PBJT atas Makanan dan/atau Minuman',
                'nama_singkat' => 'Restoran',
                'deskripsi' => 'Pajak atas pelayanan yang disediakan oleh restoran',
                'icon' => '🍽️',
                'tarif_default' => 10.00,
                'tipe_assessment' => 'self_assessment',
                'is_active' => true,
                'urutan' => 2,
            ],
            [
                'kode' => '41103',
                'nama' => 'PBJT atas Jasa Kesenian dan Hiburan',
                'nama_singkat' => 'Hiburan',
                'deskripsi' => 'Pajak atas penyelenggaraan hiburan',
                'icon' => '🎭',
                'tarif_default' => 10.00,
                'tipe_assessment' => 'self_assessment',
                'is_active' => true,
                'urutan' => 3,
            ],
            [
                'kode' => '41104',
                'nama' => 'Pajak Reklame',
                'nama_singkat' => 'Reklame',
                'deskripsi' => 'Pajak atas penyelenggaraan reklame',
                'icon' => '🪧',
                'tarif_default' => 25.00,
                'tipe_assessment' => 'official_assessment',
                'is_active' => true,
                'urutan' => 4,
            ],
            [
                'kode' => '41105',
                'nama' => 'Pajak Penerangan Jalan',
                'nama_singkat' => 'PPJ',
                'deskripsi' => 'Pajak atas penggunaan tenaga listrik',
                'icon' => '💡',
                'tarif_default' => 10.00,
                'tipe_assessment' => 'self_assessment',
                'is_active' => true,
                'urutan' => 5,
            ],
            [
                'kode' => '41106',
                'nama' => 'Pajak Mineral Bukan Logam dan Batuan',
                'nama_singkat' => 'MBLB',
                'deskripsi' => 'Pajak atas kegiatan pengambilan mineral bukan logam dan batuan',
                'icon' => '⛏️',
                'tarif_default' => 20.00,
                'opsen_persen' => 25.00,
                'tipe_assessment' => 'self_assessment',
                'is_active' => true,
                'urutan' => 6,
            ],
            [
                'kode' => '41107',
                'nama' => 'PBJT atas Jasa Parkir',
                'nama_singkat' => 'Parkir',
                'deskripsi' => 'Pajak atas penyelenggaraan tempat parkir',
                'icon' => '🅿️',
                'tarif_default' => 20.00,
                'tipe_assessment' => 'self_assessment',
                'is_active' => true,
                'urutan' => 7,
            ],
            [
                'kode' => '41108',
                'nama' => 'Pajak Air Tanah',
                'nama_singkat' => 'Air Tanah',
                'deskripsi' => 'Pajak atas pengambilan dan pemanfaatan air tanah',
                'icon' => '💧',
                'tarif_default' => 20.00,
                'tipe_assessment' => 'official_assessment',
                'is_active' => true,
                'urutan' => 8,
            ],
            [
                'kode' => '41109',
                'nama' => 'Pajak Sarang Burung Walet',
                'nama_singkat' => 'Sarang Walet',
                'deskripsi' => 'Pajak atas pengambilan dan/atau pengusahaan sarang burung walet',
                'icon' => '🐦',
                'tarif_default' => 10.00,
                'tipe_assessment' => 'self_assessment',
                'is_active' => true,
                'urutan' => 9,
            ],
        ];

        foreach ($jenisPajak as $data) {
            JenisPajak::updateOrCreate(
                ['kode' => $data['kode']],
                $data
            );
        }
    }
}
