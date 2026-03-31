<?php

namespace Database\Seeders;

use App\Domain\Reklame\Models\KelompokLokasiJalan;
use Illuminate\Database\Seeder;

class KelompokLokasiJalanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'A' => [
                'desc' => 'Jalan Utama/Protokol',
                'streets' => [
                    'Kawasan Alun-Alun', 'Kawasan Bisnis', 'Kawasan Pasar', 'Kawasan Wisata',
                    'Persimpangan Jalan', 'Jalan A. Yani', 'Jalan Gajah Mada',
                    'Jalan Untung Suropati', 'Jalan Diponegoro', 'Jalan HOS Cokroaminoto',
                    'Jalan KH Hasyim Asyari', 'Jalan Kopral Kasan', 'Jalan M.H. Thamrin',
                    'Jalan Mastumapel', 'Jalan Pahlawan', 'Jalan Panglima Sudirman',
                    'Jalan Pasar', 'Jalan Pemuda', 'Jalan Teuku Umar', 'Jalan Veteran',
                ],
            ],
            'A1' => [
                'desc' => 'Jalan Sekunder Utama',
                'streets' => [
                    'Jalan Nasional (selain lampiran)', 'Jalan Provinsi (selain lampiran)',
                    'Jalan AKBP M. Suroko', 'Jalan Imam Bonjol', 'Jalan KH. Mansyur',
                    'Jalan Lisman', 'Jalan Mastrip', 'Jalan Pemuda - Semanding',
                    'Jalan Trunojoyo', 'Jalan Rajekwesi',
                ],
            ],
            'A2' => [
                'desc' => 'Jalan Sekunder',
                'streets' => [
                    'Jalan Basuki Rahmat', 'Jalan Dr. Cipto', 'Jalan Dr. Wahidin',
                    'Jalan Hayam Wuruk', 'Jalan Jaksa Agung Suprapto', 'Jalan Kartini',
                    'Jalan Lettu Suwolo', 'Jalan Lettu Suyitno', 'Jalan Mayangkoro',
                    'Jalan Mayjen Panjaitan', 'Jalan Monginsidi', 'Jalan Panglima Polim',
                    'Jalan Pattimura', 'Jalan Rajawali', 'Jalan Sawunggaling',
                    'Jalan Serma Abdullah', 'Jalan Tentara Genia Pelajar', 'Jalan WR. Supratman',
                ],
            ],
            'A3' => [
                'desc' => 'Jalan Lokal Utama',
                'streets' => [
                    'Jalan Ade Irma Suryani', 'Jalan Arif Rahman Hakim', 'Jalan Brigjen Sutoyo',
                    'Jalan Cut Nyak Dien', 'Jalan Dewi Sartika', 'Jalan Dr. Suharso',
                    'Jalan Dr. Sutomo', 'Jalan H. Agus Salim', 'Jalan Hartono',
                    'Jalan Hasanudin', 'Jalan Kapten Martono', 'Jalan Kapten Ramli',
                    'Jalan Kapten Sumitro', 'Jalan Kapten Tendean', 'Jalan KH. Achmad Dahlan',
                    'Jalan Ki Hajar Dewantara', 'Jalan Kolonel Sugiono', 'Jalan KS. Tubun',
                    'Jalan Kusnandar', 'Jalan Kyai Mojo', 'Jalan Kyai Sulaiman',
                    'Jalan Letda Mustajab', 'Jalan Letda Suraji', 'Jalan Mangga',
                    'Jalan Mliwis Putih', 'Jalan R. Sunjani', 'Jalan Sari Mulyo',
                    'Jalan Serma Darsi', 'Jalan Serma Kusman', 'Jalan Serma Maun',
                    'Jalan Sersan Mulyono', 'Jalan Sersan Suratman', 'Jalan Setyo Budi',
                    'Jalan Tritunggal',
                ],
            ],
            'B' => [
                'desc' => 'Jalan Lokal',
                'streets' => [
                    'Jalan Kabupaten (selain yang disebutkan)',
                    'Jalan Ibu Kota Kecamatan',
                ],
            ],
            'C' => [
                'desc' => 'Jalan Lingkungan',
                'streets' => [
                    'Jalan Lingkungan',
                ],
            ],
        ];

        foreach ($data as $kelompok => $info) {
            foreach ($info['streets'] as $street) {
                KelompokLokasiJalan::updateOrCreate(
                    [
                        'kelompok' => $kelompok,
                        'nama_jalan' => $street,
                    ],
                    [
                        'deskripsi' => $info['desc'],
                        'is_active' => true,
                        'berlaku_mulai' => '2026-01-01',
                        'berlaku_sampai' => null,
                    ]
                );
            }
        }
    }
}
