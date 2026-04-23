<?php

namespace App\Domain\Retribusi\Services;

use App\Domain\Master\Models\JenisPajak;
use App\Domain\Retribusi\Models\ObjekRetribusiSewaTanah;
use App\Domain\Retribusi\Models\SkrdSewaRetribusi;
use App\Domain\Retribusi\Models\TarifSewaTanah;
use Exception;
use Illuminate\Support\Facades\DB;

class RetribusiSewaTanahService
{
    public function calculateRetribusi(
        string $subJenisPajakId,
        float $luasM2,
        int $jumlahReklame,
        int $durasi,
        float $tarifPajakPersen = 25.00,
        ?string $tanggalReferensi = null,
    ): array {
        $tarif = TarifSewaTanah::lookupTarif($subJenisPajakId, $tanggalReferensi);

        if (! $tarif) {
            throw new Exception('Tarif tidak ditemukan untuk sub jenis retribusi yang dipilih.');
        }

        $satuanLabel = match ($tarif->satuan_waktu) {
            'perTahun' => 'per Tahun',
            'perBulan' => 'per Bulan',
            default => $tarif->satuan_waktu,
        };

        $tarifNominal = (float) $tarif->tarif_nominal;

        $jumlahRetribusi = $luasM2 * $jumlahReklame * $tarifNominal * $durasi;

        return [
            'tarif_nominal' => $tarifNominal,
            'satuan_waktu' => $tarif->satuan_waktu,
            'satuan_label' => $satuanLabel,
            'luas_m2' => $luasM2,
            'jumlah_reklame' => $jumlahReklame,
            'tarif_pajak_persen' => $tarifPajakPersen,
            'durasi' => $durasi,
            'jumlah_retribusi' => round($jumlahRetribusi),
        ];
    }

    public function createDraftSkrd(array $data): SkrdSewaRetribusi
    {
        $objekRetribusi = ObjekRetribusiSewaTanah::findOrFail($data['objek_retribusi_id']);
        $subJenisPajakId = $objekRetribusi->sub_jenis_pajak_id;

        if (! $subJenisPajakId) {
            throw new Exception('Objek retribusi belum memiliki sub jenis retribusi.');
        }

        $candidates = SkrdSewaRetribusi::whereIn('status', ['draft', 'disetujui'])
            ->where('sub_jenis_pajak_id', $subJenisPajakId)
            ->where('masa_berlaku_sampai', '>=', $data['masa_berlaku_mulai'])
            ->where('masa_berlaku_mulai', '<=', $data['masa_berlaku_sampai'])
            ->get();

        $existing = ! empty($data['nik_wajib_pajak'])
            ? $candidates->first(fn (SkrdSewaRetribusi $r) => $r->nik_wajib_pajak === $data['nik_wajib_pajak'])
            : $candidates->first();

        if ($existing) {
            throw new Exception('Sudah ada SKRD aktif (No: ' . $existing->nomor_skrd . ') dengan masa retribusi yang masih berlaku untuk sub jenis dan wajib bayar yang sama.');
        }

        $luasM2 = (float) $objekRetribusi->luas_m2;

        $tarifPajakPersen = (float) ($data['tarif_pajak_persen']
            ?? JenisPajak::where('kode', '41104')->value('tarif_default')
            ?? 25.00);

        return DB::transaction(function () use ($data, $objekRetribusi, $luasM2, $tarifPajakPersen, $subJenisPajakId) {
            $calc = $this->calculateRetribusi(
                $subJenisPajakId,
                $luasM2,
                (int) $data['jumlah_reklame'],
                (int) $data['durasi'],
                $tarifPajakPersen,
                $data['masa_berlaku_mulai'] ?? null,
            );

            $jenisPajak = JenisPajak::where('kode', '42101')->first();

            return SkrdSewaRetribusi::create([
                'nomor_skrd' => SkrdSewaRetribusi::generateNomorSkrd() . ' (DRAFT)',
                'jenis_pajak_id' => $jenisPajak?->id,
                'sub_jenis_pajak_id' => $subJenisPajakId,
                'objek_retribusi_id' => $objekRetribusi->id,
                'npwpd' => $data['npwpd'] ?? $objekRetribusi->npwpd,
                'nik_wajib_pajak' => $data['nik_wajib_pajak'] ?? $objekRetribusi->nik,
                'nama_wajib_pajak' => $data['nama_wajib_pajak'] ?? $objekRetribusi->nama_pemilik,
                'alamat_wajib_pajak' => $data['alamat_wajib_pajak'] ?? $objekRetribusi->alamat_pemilik ?? '-',
                'nama_objek' => $data['nama_objek'] ?? $objekRetribusi->nama_objek,
                'alamat_objek' => $data['alamat_objek'] ?? $objekRetribusi->alamat_objek,
                'luas_m2' => $calc['luas_m2'],
                'jumlah_reklame' => $calc['jumlah_reklame'],
                'tarif_pajak_persen' => $calc['tarif_pajak_persen'],
                'tarif_nominal' => $calc['tarif_nominal'],
                'satuan_waktu' => $calc['satuan_waktu'],
                'satuan_label' => $calc['satuan_label'],
                'durasi' => $calc['durasi'],
                'jumlah_retribusi' => $calc['jumlah_retribusi'],
                'masa_berlaku_mulai' => $data['masa_berlaku_mulai'],
                'masa_berlaku_sampai' => $data['masa_berlaku_sampai'],
                'status' => 'draft',
                'tanggal_buat' => now(),
                'petugas_id' => $data['petugas_id'],
                'petugas_nama' => $data['petugas_nama'],
            ]);
        });
    }
}
