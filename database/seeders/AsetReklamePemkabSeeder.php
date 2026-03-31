<?php

namespace Database\Seeders;

use App\Domain\Reklame\Models\AsetReklamePemkab;
use Illuminate\Database\Seeder;

class AsetReklamePemkabSeeder extends Seeder
{
    public function run(): void
    {
        $neonbox = [
            ['kode_aset' => 'NB001', 'lokasi' => 'Jl. A. Yani', 'keterangan' => 'Depan UNIGIRI', 'panjang' => 1.8, 'lebar' => 0.8, 'jumlah_muka' => 2, 'traffic' => 'Sangat Tinggi', 'kelompok_lokasi' => 'A', 'harga_sewa_per_tahun' => '3819000', 'harga_sewa_per_bulan' => '498000', 'harga_sewa_per_minggu' => '124000', 'latitude' => -7.1686316, 'longitude' => 111.8925701],
            ['kode_aset' => 'NB002', 'lokasi' => 'Jl. Veteran', 'keterangan' => 'Depan KDS', 'panjang' => 1.8, 'lebar' => 0.8, 'jumlah_muka' => 2, 'traffic' => 'Sangat Tinggi', 'kelompok_lokasi' => 'A', 'harga_sewa_per_tahun' => '3942000', 'harga_sewa_per_bulan' => '512000', 'harga_sewa_per_minggu' => '128000', 'latitude' => -7.1628573, 'longitude' => 111.8974929],
            ['kode_aset' => 'NB003', 'lokasi' => 'Jl. M.T. Haryono', 'keterangan' => null, 'panjang' => 1.8, 'lebar' => 0.8, 'jumlah_muka' => 2, 'traffic' => 'Sangat Tinggi', 'kelompok_lokasi' => 'A1', 'harga_sewa_per_tahun' => '4054000', 'harga_sewa_per_bulan' => '522000', 'harga_sewa_per_minggu' => '130000', 'latitude' => -7.166653, 'longitude' => 111.8640533],
            ['kode_aset' => 'NB004', 'lokasi' => 'Jl. Gajah Mada', 'keterangan' => 'Depan Ruko PJKA Timur', 'panjang' => 0.8, 'lebar' => 0.4, 'jumlah_muka' => 2, 'traffic' => 'Sangat Tinggi', 'kelompok_lokasi' => 'A', 'harga_sewa_per_tahun' => '4535000', 'harga_sewa_per_bulan' => '577000', 'harga_sewa_per_minggu' => '144000', 'latitude' => -7.162497, 'longitude' => 111.8848765],
            ['kode_aset' => 'NB005', 'lokasi' => 'Jl. Diponegoro', 'keterangan' => 'Utara perempatan Ade Irma-Brigjen Sutoyo', 'panjang' => 0.8, 'lebar' => 0.4, 'jumlah_muka' => 2, 'traffic' => 'Sangat Tinggi', 'kelompok_lokasi' => 'A', 'harga_sewa_per_tahun' => '4535000', 'harga_sewa_per_bulan' => '577000', 'harga_sewa_per_minggu' => '144000', 'latitude' => -7.1583541, 'longitude' => 111.8819637],
        ];

        $billboard = [
            ['kode_aset' => 'BB001', 'lokasi' => 'Depan Terminal Betek Gondang', 'keterangan' => null, 'panjang' => 3, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Kawasan Terminal', 'kelompok_lokasi' => 'C', 'harga_sewa_per_tahun' => '21543000', 'harga_sewa_per_bulan' => '2953000', 'harga_sewa_per_minggu' => '984000', 'latitude' => -7.4031966, 'longitude' => 111.8706301],
            ['kode_aset' => 'BB002', 'lokasi' => 'Pertigaan Desa Luwihaji', 'keterangan' => 'Dekat TBB titik A', 'panjang' => 3, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Perbatasan', 'kelompok_lokasi' => 'C', 'harga_sewa_per_tahun' => '23873000', 'harga_sewa_per_bulan' => '2962000', 'harga_sewa_per_minggu' => '1069000', 'latitude' => -7.2519796, 'longitude' => 111.4895317],
            ['kode_aset' => 'BB003', 'lokasi' => 'Pertigaan Desa Luwihaji', 'keterangan' => 'Dekat TBB titik B', 'panjang' => 3, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Perbatasan', 'kelompok_lokasi' => 'C', 'harga_sewa_per_tahun' => '23873000', 'harga_sewa_per_bulan' => '2962000', 'harga_sewa_per_minggu' => '1069000', 'latitude' => -7.2520000, 'longitude' => 111.4896000],
            ['kode_aset' => 'BB004', 'lokasi' => 'Pertigaan Kepohbaru', 'keterangan' => null, 'panjang' => 3, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Persimpangan Strategis', 'kelompok_lokasi' => 'C', 'harga_sewa_per_tahun' => '24542000', 'harga_sewa_per_bulan' => '2981000', 'harga_sewa_per_minggu' => '1077000', 'latitude' => -7.2290941, 'longitude' => 112.0873677],
            ['kode_aset' => 'BB005', 'lokasi' => 'Jl. Raya Ngraho', 'keterangan' => 'Utara Kecamatan', 'panjang' => 3, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Perkantoran', 'kelompok_lokasi' => 'C', 'harga_sewa_per_tahun' => '24670000', 'harga_sewa_per_bulan' => '3062000', 'harga_sewa_per_minggu' => '1105000', 'latitude' => -7.2478559, 'longitude' => 111.5345793],
            ['kode_aset' => 'BB006', 'lokasi' => 'Depan Polsek Kasiman', 'keterangan' => null, 'panjang' => 3, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Fasilitas Umum', 'kelompok_lokasi' => 'C', 'harga_sewa_per_tahun' => '24749000', 'harga_sewa_per_bulan' => '3284000', 'harga_sewa_per_minggu' => '1237000', 'latitude' => -7.1454164, 'longitude' => 111.6204183],
            ['kode_aset' => 'BB007', 'lokasi' => 'Desa Semambung Kec. Kanor', 'keterangan' => 'Utara Jembatan Kare sisi BJN', 'panjang' => 4, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Dekat Jembatan Kanor-Rengel', 'kelompok_lokasi' => 'C', 'harga_sewa_per_tahun' => '25303000', 'harga_sewa_per_bulan' => '3803000', 'harga_sewa_per_minggu' => '1243000', 'latitude' => -7.0718199, 'longitude' => 112.0169049],
            ['kode_aset' => 'BB008', 'lokasi' => 'Desa Semambung Kec. Kanor', 'keterangan' => 'Utara Jembatan Kare sisi TBN', 'panjang' => 4, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Dekat Jembatan Kanor-Rengel', 'kelompok_lokasi' => 'C', 'harga_sewa_per_tahun' => '25303000', 'harga_sewa_per_bulan' => '3803000', 'harga_sewa_per_minggu' => '1243000', 'latitude' => -7.0719000, 'longitude' => 112.0170000],
            ['kode_aset' => 'BB009', 'lokasi' => 'Jl. Raya Dander', 'keterangan' => 'Depan Ktr. Kec', 'panjang' => 3, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Akses Wisata', 'kelompok_lokasi' => 'B', 'harga_sewa_per_tahun' => '26218000', 'harga_sewa_per_bulan' => '3833000', 'harga_sewa_per_minggu' => '1258000', 'latitude' => -7.2503125, 'longitude' => 111.8461075],
            ['kode_aset' => 'BB010', 'lokasi' => 'Perempatan Pasar Kedungadem', 'keterangan' => null, 'panjang' => 4, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Pasar & Pertokoan', 'kelompok_lokasi' => 'B', 'harga_sewa_per_tahun' => '26615000', 'harga_sewa_per_bulan' => '3844000', 'harga_sewa_per_minggu' => '1296000', 'latitude' => -7.3019073, 'longitude' => 112.0467087],
            ['kode_aset' => 'BB011', 'lokasi' => 'Jl. Raya Baureno', 'keterangan' => 'Dekat Rel KA Babat', 'panjang' => 3, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Permukiman & Kereta Api', 'kelompok_lokasi' => 'B', 'harga_sewa_per_tahun' => '27658000', 'harga_sewa_per_bulan' => '3645000', 'harga_sewa_per_minggu' => '1342000', 'latitude' => -7.1177051, 'longitude' => 112.1513769],
            ['kode_aset' => 'BB012', 'lokasi' => 'Jl. Raya Clangap', 'keterangan' => 'Perempatan Clangap', 'panjang' => 4, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Pertokoan Strategis', 'kelompok_lokasi' => 'B', 'harga_sewa_per_tahun' => '28233000', 'harga_sewa_per_bulan' => '3846000', 'harga_sewa_per_minggu' => '1346000', 'latitude' => -7.1370322, 'longitude' => 111.7258363],
            ['kode_aset' => 'BB013', 'lokasi' => 'Jl. Raya Sumberrejo', 'keterangan' => 'Perempatan Sumberrejo', 'panjang' => 4, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Pasar & Pertokoan', 'kelompok_lokasi' => 'B', 'harga_sewa_per_tahun' => '30297000', 'harga_sewa_per_bulan' => '4008000', 'harga_sewa_per_minggu' => '1479000', 'latitude' => -7.1772566, 'longitude' => 112.0004722],
            ['kode_aset' => 'BB014', 'lokasi' => 'Jl. A. Yani', 'keterangan' => 'Barat Jembatan Tikusan', 'panjang' => 4, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Permukiman', 'kelompok_lokasi' => 'A', 'harga_sewa_per_tahun' => '33621000', 'harga_sewa_per_bulan' => '4985000', 'harga_sewa_per_minggu' => '1681000', 'latitude' => -7.1739455, 'longitude' => 111.8991283],
            ['kode_aset' => 'BB015', 'lokasi' => 'Jl. Basuki Rachmad', 'keterangan' => 'Perempatan PLN', 'panjang' => 4, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Kawasan Perkotaan', 'kelompok_lokasi' => 'A2', 'harga_sewa_per_tahun' => '33643000', 'harga_sewa_per_bulan' => '4991000', 'harga_sewa_per_minggu' => '1686000', 'latitude' => -7.1527882, 'longitude' => 111.8915997],
            ['kode_aset' => 'BB016', 'lokasi' => 'Jetak sisi barat', 'keterangan' => null, 'panjang' => 4, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Pertokoan & Permukiman', 'kelompok_lokasi' => 'A1', 'harga_sewa_per_tahun' => '39915400', 'harga_sewa_per_bulan' => '5922000', 'harga_sewa_per_minggu' => '2000600', 'latitude' => -7.1640974, 'longitude' => 111.8672906],
            ['kode_aset' => 'BB017', 'lokasi' => 'Jetak sisi timur', 'keterangan' => null, 'panjang' => 4, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Pertokoan & Permukiman', 'kelompok_lokasi' => 'A1', 'harga_sewa_per_tahun' => '39915400', 'harga_sewa_per_bulan' => '5922000', 'harga_sewa_per_minggu' => '2000600', 'latitude' => -7.1641000, 'longitude' => 111.8673500],
            ['kode_aset' => 'BB018', 'lokasi' => 'Perempatan Diponegoro', 'keterangan' => null, 'panjang' => 3, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Komersil & Perkantoran', 'kelompok_lokasi' => 'A', 'harga_sewa_per_tahun' => '40124000', 'harga_sewa_per_bulan' => '5984300', 'harga_sewa_per_minggu' => '2040500', 'latitude' => -7.1524073, 'longitude' => 111.8825776],
            ['kode_aset' => 'BB019', 'lokasi' => 'Jembatan Sosrodilogo B', 'keterangan' => null, 'panjang' => 3, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Pertokoan & Permukiman', 'kelompok_lokasi' => 'A', 'harga_sewa_per_tahun' => '40124000', 'harga_sewa_per_bulan' => '5984300', 'harga_sewa_per_minggu' => '2040500', 'latitude' => -7.1557002, 'longitude' => 111.8720678],
            ['kode_aset' => 'BB020', 'lokasi' => 'Jl. Veteran', 'keterangan' => 'Perempatan Mlaten', 'panjang' => 4, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Kawasan Bisnis', 'kelompok_lokasi' => 'A', 'harga_sewa_per_tahun' => '41596800', 'harga_sewa_per_bulan' => '5999000', 'harga_sewa_per_minggu' => '2067800', 'latitude' => -7.1537736, 'longitude' => 111.9008949],
            ['kode_aset' => 'BB021', 'lokasi' => 'Jembatan Sosrodilogo A', 'keterangan' => null, 'panjang' => 4, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Jembatan Penghubung', 'kelompok_lokasi' => 'A', 'harga_sewa_per_tahun' => '41596800', 'harga_sewa_per_bulan' => '5999000', 'harga_sewa_per_minggu' => '2067800', 'latitude' => -7.1558000, 'longitude' => 111.8721000],
            ['kode_aset' => 'BB022', 'lokasi' => "Jl. Hasyim Asy'ari", 'keterangan' => 'Barat Polwil', 'panjang' => 4, 'lebar' => 6, 'jumlah_muka' => 1, 'kawasan' => 'Pusat Kota & Pasar', 'kelompok_lokasi' => 'A', 'harga_sewa_per_tahun' => '44068500', 'harga_sewa_per_bulan' => '6573000', 'harga_sewa_per_minggu' => '2240700', 'latitude' => -7.1483404, 'longitude' => 111.8792772],
            ['kode_aset' => 'BB023', 'lokasi' => 'Jl. Gajahmada', 'keterangan' => 'Barat Stasiun', 'panjang' => 4, 'lebar' => 8, 'jumlah_muka' => 1, 'kawasan' => 'Kawasan Bisnis & Stasiun', 'kelompok_lokasi' => 'A', 'harga_sewa_per_tahun' => '45441900', 'harga_sewa_per_bulan' => '6954500', 'harga_sewa_per_minggu' => '2363200', 'latitude' => -7.1637663, 'longitude' => 111.8866235],
        ];

        foreach ($neonbox as $data) {
            AsetReklamePemkab::updateOrCreate(
                ['kode_aset' => $data['kode_aset']],
                array_merge($data, [
                    'nama'   => 'Neon Box ' . $data['lokasi'],
                    'jenis'  => 'neon_box',
                    'luas_m2' => $data['panjang'] * $data['lebar'],
                    'status_ketersediaan' => 'tersedia',
                    'is_active' => true,
                ])
            );
        }

        foreach ($billboard as $data) {
            AsetReklamePemkab::updateOrCreate(
                ['kode_aset' => $data['kode_aset']],
                array_merge($data, [
                    'nama'   => 'Billboard ' . $data['lokasi'],
                    'jenis'  => 'billboard',
                    'luas_m2' => $data['panjang'] * $data['lebar'],
                    'status_ketersediaan' => 'tersedia',
                    'is_active' => true,
                ])
            );
        }

        $this->command->info('Seeded ' . count($neonbox) . ' neon box + ' . count($billboard) . ' billboard = ' . (count($neonbox) + count($billboard)) . ' aset reklame pemkab.');
    }
}
