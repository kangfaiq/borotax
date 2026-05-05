<?php

namespace App\Domain\Reklame\Services;

use RuntimeException;
use Exception;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Reklame\Models\ReklameNilaiStrategis;
use App\Domain\Reklame\Models\ReklameTariff;
use App\Domain\Reklame\Models\SkpdReklame;
use Illuminate\Support\Facades\DB;

class ReklameService
{
    /**
     * Calculate reklame tax (formula baru sesuai spesifikasi teknis).
     *
     * NSPR + NJOPR → Dasar Pengenaan per m²
     * PAJAK (tarif_pokok) = (NSPR + NJOPR) × 25%
     * POKOK DASAR = tarif_pokok × luas × muka × durasi × jumlah_reklame
     * POKOK PENYESUAIAN = POKOK DASAR × penyesuaian_lokasi × penyesuaian_produk
     * TOTAL PAJAK = POKOK PENYESUAIAN + nilai_strategis
     */
    public function calculateTax(
        string $hargaPatokanReklameId,
        ?string $kelompokLokasi,
        string $satuanWaktu,
        float $luasM2,
        int $jumlahMuka,
        int $durasi,
        int $jumlahReklame,
        string $lokasiPenempatan,
        string $jenisProduk,
        ?string $tanggalReferensi = null
    ): array {
        // 1. Lookup tarif record (includes nspr, njopr, tarif_pokok)
        $record = ReklameTariff::lookupRecord($hargaPatokanReklameId, $kelompokLokasi, $satuanWaktu, $tanggalReferensi);
        if ($record === null) {
            throw new RuntimeException('Tarif pokok tidak ditemukan untuk kombinasi sub jenis, kelompok, dan satuan waktu yang dipilih.');
        }

        $tarifPokok = (float) $record->tarif_pokok;
        $nspr = (float) ($record->nspr ?? 0);
        $njopr = (float) ($record->njopr ?? 0);
        $satuanLabel = $record->satuan_label;

        // 2. Hitung penyesuaian
        $penyesuaianLokasi = $lokasiPenempatan === 'dalam_ruangan' ? 0.25 : 1.00;
        $penyesuaianProduk = $jenisProduk === 'rokok' ? 1.10 : 1.00;

        // 3. Pokok pajak dasar
        $pokokDasar = $tarifPokok * $luasM2 * $jumlahMuka * $durasi * $jumlahReklame;

        // 4. Pokok setelah penyesuaian
        $pokokPenyesuaian = $pokokDasar * $penyesuaianLokasi * $penyesuaianProduk;

        // 5. Nilai strategis (hanya reklame tetap)
        $nilaiStrategis = 0;
        $hargaPatokanReklame = HargaPatokanReklame::find($hargaPatokanReklameId);
        if ($hargaPatokanReklame && !$hargaPatokanReklame->is_insidentil && $kelompokLokasi) {
            $nilaiStrategis = ReklameNilaiStrategis::hitungNilaiStrategis(
                $kelompokLokasi,
                $luasM2,
                $satuanWaktu,
                $durasi,
                $jumlahReklame,
                $tanggalReferensi
            );
        }

        // 6. Total pajak
        $totalPajak = $pokokPenyesuaian + $nilaiStrategis;

        return [
            'nspr' => $nspr,
            'njopr' => $njopr,
            'satuan_label' => $satuanLabel,
            'tarif_pokok' => $tarifPokok,
            'pokok_pajak_dasar' => $pokokDasar,
            'penyesuaian_lokasi' => $penyesuaianLokasi,
            'penyesuaian_produk' => $penyesuaianProduk,
            'dasar_pengenaan' => $pokokPenyesuaian,
            'nilai_strategis' => $nilaiStrategis,
            'jumlah_pajak' => $totalPajak,
        ];
    }

    /**
     * Calculate reklame tax (formula LAMA — backward compatibility).
     */
    public function calculateTaxLegacy(
        float $luasM2,
        int $jumlahMuka,
        int $durasiHari,
        float $nilaiSewa,
        float $tarifPersen
    ): array {
        $dasarPengenaan = $luasM2 * $jumlahMuka * $durasiHari * $nilaiSewa;
        $jumlahPajak = $dasarPengenaan * ($tarifPersen / 100);

        return [
            'dasar_pengenaan' => $dasarPengenaan,
            'jumlah_pajak' => $jumlahPajak,
        ];
    }

    /**
     * Create a draft SKPD Reklame (formula baru).
     */
    public function createDraftSkpd(array $data): SkpdReklame
    {
        // Cek apakah sudah punya SKPD aktif dengan masa berlaku yang overlap
        $overlapQuery = SkpdReklame::whereIn('status', ['draft', 'disetujui'])
            ->where('masa_berlaku_sampai', '>=', $data['masa_berlaku_mulai'])
            ->where('masa_berlaku_mulai', '<=', $data['masa_berlaku_sampai']);

        if (!empty($data['tax_object_id'])) {
            $overlapQuery->where('tax_object_id', $data['tax_object_id']);
        } elseif (!empty($data['aset_reklame_pemkab_id'])) {
            $overlapQuery->where('aset_reklame_pemkab_id', $data['aset_reklame_pemkab_id']);
        }

        $existing = $overlapQuery->first();

        if ($existing) {
            throw new Exception('Objek reklame ini sudah memiliki SKPD aktif (No: ' . $existing->nomor_skpd . ') dengan masa pajak yang masih berlaku.');
        }

        return DB::transaction(function () use ($data) {
            $calc = $this->calculateTax(
                $data['harga_patokan_reklame_id'],
                $data['kelompok_lokasi'] ?? null,
                $data['satuan_waktu'],
                $data['luas_m2'],
                $data['jumlah_muka'],
                $data['durasi'],
                $data['jumlah_reklame'] ?? 1,
                $data['lokasi_penempatan'],
                $data['jenis_produk'],
                $data['masa_berlaku_mulai'] ?? null
            );

            $jenisPajak = JenisPajak::where('kode', '41104')->first();
            $subJenisPajak = SubJenisPajak::find($data['sub_jenis_pajak_id']);
            $hargaPatokanReklame = HargaPatokanReklame::find($data['harga_patokan_reklame_id']);

            return SkpdReklame::create([
                'nomor_skpd' => SkpdReklame::generateNomorSkpd() . ' (DRAFT)',
                'tax_object_id' => $data['tax_object_id'] ?? null,
                'jenis_pajak_id' => $jenisPajak?->id ?? $data['jenis_pajak_id'],
                'sub_jenis_pajak_id' => $data['sub_jenis_pajak_id'],
                'harga_patokan_reklame_id' => $data['harga_patokan_reklame_id'],
                'npwpd' => $data['npwpd'] ?? null,
                'nik_wajib_pajak' => $data['nik_wajib_pajak'],
                'nama_wajib_pajak' => $data['nama_wajib_pajak'],
                'alamat_wajib_pajak' => $data['alamat_wajib_pajak'] ?? '-',
                'nama_reklame' => $data['nama_reklame'],
                'isi_materi_reklame' => $data['isi_materi_reklame'] ?? null,
                'jenis_reklame' => $hargaPatokanReklame?->nama ?? $subJenisPajak?->nama,
                'alamat_reklame' => $data['alamat_reklame'],
                'kelompok_lokasi' => $data['kelompok_lokasi'] ?? null,
                'bentuk' => $data['bentuk'] ?? null,
                'panjang' => $data['panjang'] ?? null,
                'lebar' => $data['lebar'] ?? null,
                'tinggi' => $data['tinggi'] ?? null,
                'sisi_atas' => $data['sisi_atas'] ?? null,
                'sisi_bawah' => $data['sisi_bawah'] ?? null,
                'diameter' => $data['diameter'] ?? null,
                'diameter2' => $data['diameter2'] ?? null,
                'alas' => $data['alas'] ?? null,
                'luas_m2' => $data['luas_m2'],
                'jumlah_muka' => $data['jumlah_muka'],
                'lokasi_penempatan' => $data['lokasi_penempatan'],
                'jenis_produk' => $data['jenis_produk'],
                'jumlah_reklame' => $data['jumlah_reklame'] ?? 1,
                'satuan_waktu' => $data['satuan_waktu'],
                'satuan_label' => $calc['satuan_label'],
                'durasi' => $data['durasi'],
                'tarif_pokok' => $calc['tarif_pokok'],
                'nspr' => $calc['nspr'],
                'njopr' => $calc['njopr'],
                'penyesuaian_lokasi' => $calc['penyesuaian_lokasi'],
                'penyesuaian_produk' => $calc['penyesuaian_produk'],
                'nilai_strategis' => $calc['nilai_strategis'],
                'pokok_pajak_dasar' => $calc['pokok_pajak_dasar'],
                'masa_berlaku_mulai' => $data['masa_berlaku_mulai'],
                'masa_berlaku_sampai' => $data['masa_berlaku_sampai'],
                'dasar_pengenaan' => $calc['dasar_pengenaan'],
                'jumlah_pajak' => $calc['jumlah_pajak'],
                'status' => 'draft',
                'tanggal_buat' => now(),
                'petugas_id' => $data['petugas_id'],
                'petugas_nama' => $data['petugas_nama'],
                'aset_reklame_pemkab_id' => $data['aset_reklame_pemkab_id'] ?? null,
                'permohonan_sewa_id' => $data['permohonan_sewa_id'] ?? null,
            ]);
        });
    }

    /**
     * Create a draft SKPD Reklame dari sewa aset pemkab (tarif tetap).
     * Tidak menggunakan lookup tarif / kelompok lokasi / nilai strategis.
     * Harga sewa sudah fix dari data aset.
     */
    public function createDraftSkpdSewa(array $data): SkpdReklame
    {
        // Cek overlap masa berlaku
        $overlapQuery = SkpdReklame::whereIn('status', ['draft', 'disetujui'])
            ->where('masa_berlaku_sampai', '>=', $data['masa_berlaku_mulai'])
            ->where('masa_berlaku_mulai', '<=', $data['masa_berlaku_sampai'])
            ->where('aset_reklame_pemkab_id', $data['aset_reklame_pemkab_id']);

        $existing = $overlapQuery->first();
        if ($existing) {
            throw new Exception('Aset reklame ini sudah memiliki SKPD aktif (No: ' . $existing->nomor_skpd . ') dengan masa pajak yang masih berlaku.');
        }

        return DB::transaction(function () use ($data) {
            $hargaSewa = (float) $data['harga_sewa'];
            $durasi    = (int) $data['durasi'];

            $jumlahPajak = $hargaSewa * $durasi;

            $jenisPajak    = JenisPajak::where('kode', '41104')->first();
            $subJenisPajak = SubJenisPajak::find($data['sub_jenis_pajak_id']);
            $hargaPatokanReklame = !empty($data['harga_patokan_reklame_id'])
                ? HargaPatokanReklame::find($data['harga_patokan_reklame_id'])
                : null;

            $satuanLabel = match ($data['satuan_waktu']) {
                'perTahun'  => 'per Tahun',
                'perBulan'  => 'per Bulan',
                'perMinggu' => 'per Minggu',
                default     => $data['satuan_waktu'],
            };

            return SkpdReklame::create([
                'nomor_skpd'             => SkpdReklame::generateNomorSkpd() . ' (DRAFT)',
                'tax_object_id'          => null,
                'jenis_pajak_id'         => $jenisPajak?->id,
                'sub_jenis_pajak_id'     => $data['sub_jenis_pajak_id'],
                'harga_patokan_reklame_id' => $data['harga_patokan_reklame_id'] ?? null,
                'npwpd'                  => $data['npwpd'] ?? null,
                'nik_wajib_pajak'        => $data['nik_wajib_pajak'],
                'nama_wajib_pajak'       => $data['nama_wajib_pajak'],
                'alamat_wajib_pajak'     => $data['alamat_wajib_pajak'] ?? '-',
                'nama_reklame'           => $data['nama_reklame'],
                'isi_materi_reklame'     => $data['isi_materi_reklame'] ?? null,
                'jenis_reklame'          => $hargaPatokanReklame?->nama ?? $subJenisPajak?->nama,
                'alamat_reklame'         => $data['alamat_reklame'],
                'kelompok_lokasi'        => null,
                'bentuk'                 => $data['bentuk'] ?? null,
                'panjang'                => $data['panjang'] ?? null,
                'lebar'                  => $data['lebar'] ?? null,
                'luas_m2'                => $data['luas_m2'],
                'jumlah_muka'            => $data['jumlah_muka'],
                'lokasi_penempatan'      => 'luar_ruangan',
                'jenis_produk'           => 'non_rokok',
                'jumlah_reklame'         => 1,
                'satuan_waktu'           => $data['satuan_waktu'],
                'satuan_label'           => $satuanLabel,
                'durasi'                 => $durasi,
                'tarif_pokok'            => $hargaSewa,
                'nspr'                   => 0,
                'njopr'                  => 0,
                'penyesuaian_lokasi'     => 1.00,
                'penyesuaian_produk'     => 1.00,
                'nilai_strategis'        => 0,
                'pokok_pajak_dasar'      => $jumlahPajak,
                'masa_berlaku_mulai'     => $data['masa_berlaku_mulai'],
                'masa_berlaku_sampai'    => $data['masa_berlaku_sampai'],
                'dasar_pengenaan'        => $jumlahPajak,
                'jumlah_pajak'           => $jumlahPajak,
                'status'                 => 'draft',
                'tanggal_buat'           => now(),
                'petugas_id'             => $data['petugas_id'],
                'petugas_nama'           => $data['petugas_nama'],
                'aset_reklame_pemkab_id' => $data['aset_reklame_pemkab_id'],
                'permohonan_sewa_id'     => $data['permohonan_sewa_id'] ?? null,
            ]);
        });
    }

    /**
     * Create draft SKPD from permohonan sewa online.
     */
    public function createDraftSkpdFromPermohonan(PermohonanSewaReklame $permohonan, array $skpdData): SkpdReklame
    {
        $aset = $permohonan->asetReklame;

        $mergedData = array_merge($skpdData, [
            'aset_reklame_pemkab_id' => $permohonan->aset_reklame_pemkab_id,
            'permohonan_sewa_id'     => $permohonan->id,
            'nama_reklame'           => $aset->nama,
            'isi_materi_reklame'     => $permohonan->jenis_reklame_dipasang,
            'alamat_reklame'         => $aset->lokasi,
            'luas_m2'               => $aset->luas_m2,
            'jumlah_muka'           => $aset->jumlah_muka,
            'panjang'               => $aset->panjang,
            'lebar'                 => $aset->lebar,
            'kelompok_lokasi'       => $aset->kelompok_lokasi ?? $skpdData['kelompok_lokasi'] ?? null,
            'nik_wajib_pajak'       => $permohonan->nik,
            'nama_wajib_pajak'      => $permohonan->nama,
            'alamat_wajib_pajak'    => $permohonan->alamat,
        ]);

        $skpd = $this->createDraftSkpd($mergedData);

        // Update permohonan status
        $permohonan->update([
            'status'          => 'diproses',
            'tanggal_diproses' => now(),
            'skpd_id'         => $skpd->id,
            'petugas_id'      => $skpdData['petugas_id'],
            'petugas_nama'    => $skpdData['petugas_nama'],
        ]);

        return $skpd;
    }
}
