<?php

namespace Database\Seeders;

use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Master\Models\JenisPajak;
use Illuminate\Database\Seeder;

class SubJenisPajakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get jenis pajak IDs
        $hotel = JenisPajak::where('kode', '41101')->first();
        $restoran = JenisPajak::where('kode', '41102')->first();
        $hiburan = JenisPajak::where('kode', '41103')->first();
        $reklame = JenisPajak::where('kode', '41104')->first();
        $ppj = JenisPajak::where('kode', '41105')->first();
        $mblb = JenisPajak::where('kode', '41106')->first();
        $parkir = JenisPajak::where('kode', '41107')->first();
        $airTanah = JenisPajak::where('kode', '41108')->first();

        $allSubs = [];

        // Sub Jenis Pajak Hotel
        if ($hotel) {
            $allSubs[] = ['jenis_pajak_id' => $hotel->id, 'kode' => 'PBJT_HOTEL', 'nama' => 'PBJT-Hotel', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 1];
            $allSubs[] = ['jenis_pajak_id' => $hotel->id, 'kode' => 'PBJT_MOTEL', 'nama' => 'PBJT-Motel', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 2];
            $allSubs[] = ['jenis_pajak_id' => $hotel->id, 'kode' => 'PBJT_LOSMEN', 'nama' => 'PBJT-Losmen', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 3];
        }

        // Sub Jenis Pajak Restoran
        if ($restoran) {
            $allSubs[] = ['jenis_pajak_id' => $restoran->id, 'kode' => 'PBJT_RESTORAN', 'nama' => 'PBJT-Restoran', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 1];
            $allSubs[] = ['jenis_pajak_id' => $restoran->id, 'kode' => 'PBJT_KATERING', 'nama' => 'PBJT-Katering', 'nama_lengkap' => 'PBJT-Penyedia Jasa Boga atau Katering', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 2];
        }

        // Sub Jenis Pajak Hiburan
        if ($hiburan) {
            $allSubs[] = ['jenis_pajak_id' => $hiburan->id, 'kode' => 'PBJT_BIOSKOP', 'nama' => 'PBJT-Bioskop', 'nama_lengkap' => 'PBJT-Tontonan Film atau Bentuk Tontonan Audio Visual Lainnya yang Dipertontonkan secara Langsung di Suatu Lokasi Tertentu', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 1];

            $allSubs[] = ['jenis_pajak_id' => $hiburan->id, 'kode' => 'PBJT_KESENIAN', 'nama' => 'PBJT-Kesenian', 'nama_lengkap' => 'PBJT-Pergelaran Kesenian, Musik, Tari, dan/atau Busana', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 2];

            $allSubs[] = ['jenis_pajak_id' => $hiburan->id, 'kode' => 'PBJT_PAMERAN', 'nama' => 'PBJT-Pameran', 'nama_lengkap' => 'PBJT-Pameran', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 3];

            $allSubs[] = ['jenis_pajak_id' => $hiburan->id, 'kode' => 'PBJT_SIRKUS', 'nama' => 'PBJT-Sirkus', 'nama_lengkap' => 'PBJT-Pertunjukan Sirkus, Akrobat, dan Sulap', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 4];

            $allSubs[] = ['jenis_pajak_id' => $hiburan->id, 'kode' => 'PBJT_PACUAN_KUDA', 'nama' => 'PBJT-Pacuan Kuda', 'nama_lengkap' => 'PBJT-Pacuan Kuda dan Perlombaan Kendaraan Bermotor', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 5];

            $allSubs[] = ['jenis_pajak_id' => $hiburan->id, 'kode' => 'PBJT_PERMAINAN_KETANGKASAN', 'nama' => 'PBJT-Permainan Ketangkasan', 'nama_lengkap' => 'PBJT-Permainan Ketangkasan', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 6];

            $allSubs[] = ['jenis_pajak_id' => $hiburan->id, 'kode' => 'PBJT_OLAHRAGA', 'nama' => 'PBJT-Olahraga', 'nama_lengkap' => 'PBJT-Olahraga Permainan dengan Menggunakan Tempat/Ruang dan/atau Peralatan dan Perlengkapan untuk Olahraga dan Kebugaran', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 7];

            $allSubs[] = ['jenis_pajak_id' => $hiburan->id, 'kode' => 'PBJT_REKREASI', 'nama' => 'PBJT-Rekreasi', 'nama_lengkap' => 'PBJT-Rekreasi Wahana Air, Wahana Ekologi, Wahana Pendidikan, Wahana Budaya, Wahana Salju, Wahana Permainan, Pemancingan, Agrowisata, dan Kebun Binatang', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 8];

            $allSubs[] = ['jenis_pajak_id' => $hiburan->id, 'kode' => 'PBJT_PANTI_PIJAT', 'nama' => 'PBJT-Panti Pijat', 'nama_lengkap' => 'PBJT-Panti Pijat dan Pijat Refleksi', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 9];

            $allSubs[] = ['jenis_pajak_id' => $hiburan->id, 'kode' => 'PBJT_HIBURAN_DEWASA', 'nama' => 'PBJT-Hiburan Dewasa', 'nama_lengkap' => 'PBJT-Distkotek, Karaoke, Kelab Malam, Bar', 'tarif_persen' => 60.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 10];

            $allSubs[] = ['jenis_pajak_id' => $hiburan->id, 'kode' => 'PBJT_MANDI_UAP_SPA', 'nama' => 'PBJT-Mandi Uap/SPA', 'nama_lengkap' => 'PBJT-Mandi Uap/SPA', 'tarif_persen' => 40.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 11];
        }

        // Sub Jenis Pajak Reklame
        if ($reklame) {
            $allSubs[] = ['jenis_pajak_id' => $reklame->id, 'kode' => 'REKLAME_TETAP', 'nama' => 'Pajak Reklame Papan/Billboard/Videotron/ Megatron', 'tarif_persen' => 25.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 1];

            $allSubs[] = ['jenis_pajak_id' => $reklame->id, 'kode' => 'REKLAME_KAIN', 'nama' => 'Pajak Reklame Kain', 'tarif_persen' => 25.00, 'is_insidentil' => true, 'is_active' => true, 'urutan' => 2];

        }

        // Sub Jenis Pajak Parkir
        if ($parkir) {
            $allSubs[] = ['jenis_pajak_id' => $parkir->id, 'kode' => 'PBJT_PARKIR', 'nama' => 'PBJT-Parkir', 'nama_lengkap' => 'PBJT-Penyediaan atau Penyelenggaraan Tempat Parkir', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 1];
        }

        // Sub Jenis Pajak PPJ (PBJT atas Tenaga Listrik)
        if ($ppj) {
            $allSubs[] = ['jenis_pajak_id' => $ppj->id, 'kode' => 'PPJ_SUMBER_LAIN', 'nama' => 'Tenaga Listrik dari Sumber Lain', 'nama_lengkap' => 'PBJT-Konsumsi Tenaga Listrik dari Sumber Lain', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 1];
            $allSubs[] = ['jenis_pajak_id' => $ppj->id, 'kode' => 'PPJ_DIHASILKAN_SENDIRI', 'nama' => 'Tenaga Listrik Dihasilkan Sendiri', 'nama_lengkap' => 'PBJT-Konsumsi Tenaga Listrik Dihasilkan Sendiri', 'tarif_persen' => 1.50, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 2];
        }

        // Sub Jenis Pajak MBLB
        if ($mblb) {
            $allSubs[] = ['jenis_pajak_id' => $mblb->id, 'kode' => 'MBLB_WP', 'nama' => 'MBLB-WP', 'nama_lengkap' => 'MBLB-Wajib Pajak', 'tarif_persen' => 20.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 1];
            $allSubs[] = ['jenis_pajak_id' => $mblb->id, 'kode' => 'MBLB_WAPU', 'nama' => 'MBLB-WAPU', 'nama_lengkap' => 'MBLB-Wajib Pungut', 'tarif_persen' => 20.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 2];
        }

        // Sub Jenis Pajak Air Tanah (single entry — auto-selected in form)
        if ($airTanah) {
            $allSubs[] = ['jenis_pajak_id' => $airTanah->id, 'kode' => 'PAT', 'nama' => 'Pajak Air Tanah', 'tarif_persen' => 20.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 1];
        }

        // Sub Jenis Pajak Sarang Burung Walet (single entry)
        $sarangWalet = JenisPajak::where('kode', '41109')->first();
        if ($sarangWalet) {
            $allSubs[] = ['jenis_pajak_id' => $sarangWalet->id, 'kode' => 'SARANG_WALET', 'nama' => 'Pajak Sarang Burung Walet', 'tarif_persen' => 10.00, 'is_insidentil' => false, 'is_active' => true, 'urutan' => 1];
        }

        foreach ($allSubs as $data) {
            SubJenisPajak::updateOrCreate(
                ['kode' => $data['kode']],
                $data
            );
        }
    }
}
