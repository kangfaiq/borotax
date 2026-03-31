<?php

namespace App\Domain\AirTanah\Services;

use Exception;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Master\Models\JenisPajak;
use Illuminate\Support\Facades\DB;

class AirTanahService
{
    /**
     * Calculate air tanah tax.
     *
     * Usage = meter_after - meter_before
     * Dasar pengenaan = usage × tarif_per_m3
     * Pajak = dasar × (tarif_persen / 100)
     */
    public function calculateTax(
        int $meterBefore,
        int $meterAfter,
        float $tarifPerM3,
        float $tarifPersen
    ): array {
        $usage = $meterAfter - $meterBefore;
        $dasarPengenaan = $usage * $tarifPerM3;
        $jumlahPajak = $dasarPengenaan * ($tarifPersen / 100);

        return [
            'usage' => $usage,
            'dasar_pengenaan' => $dasarPengenaan,
            'jumlah_pajak' => $jumlahPajak,
        ];
    }

    /**
     * Create a draft SKPD Air Tanah and update the water object meter.
     */
    public function createDraftSkpd(array $data): SkpdAirTanah
    {
        // Cek apakah objek air tanah sudah punya SKPD aktif untuk periode yang sama
        $existing = SkpdAirTanah::where('tax_object_id', $data['tax_object_id'])
            ->whereIn('status', ['draft', 'disetujui'])
            ->where('periode_bulan', $data['periode_bulan'])
            ->first();

        if ($existing) {
            throw new Exception('Objek air tanah ini sudah memiliki SKPD aktif (No: ' . $existing->nomor_skpd . ') untuk periode ' . $existing->periode_bulan . '.');
        }

        return DB::transaction(function () use ($data) {
            $calc = $this->calculateTax(
                $data['meter_reading_before'],
                $data['meter_reading_after'],
                $data['tarif_per_m3'],
                $data['tarif_persen']
            );

            $jenisPajak = JenisPajak::where('kode', '41108')->first();

            $skpd = SkpdAirTanah::create([
                'nomor_skpd' => SkpdAirTanah::generateNomorSkpd() . ' (DRAFT)',
                'tax_object_id' => $data['tax_object_id'],
                'jenis_pajak_id' => $jenisPajak?->id ?? $data['jenis_pajak_id'],
                'sub_jenis_pajak_id' => $data['sub_jenis_pajak_id'],
                'nik_wajib_pajak' => $data['nik_wajib_pajak'],
                'nama_wajib_pajak' => $data['nama_wajib_pajak'],
                'alamat_wajib_pajak' => $data['alamat_wajib_pajak'] ?? '-',
                'nama_objek' => $data['nama_objek'],
                'alamat_objek' => $data['alamat_objek'],
                'nopd' => $data['nopd'],
                'kecamatan' => $data['kecamatan'],
                'kelurahan' => $data['kelurahan'],
                'meter_reading_before' => $data['meter_reading_before'],
                'meter_reading_after' => $data['meter_reading_after'],
                'usage' => $calc['usage'],
                'periode_bulan' => $data['periode_bulan'],
                'tarif_per_m3' => $data['tarif_per_m3'],
                'tarif_persen' => $data['tarif_persen'],
                'dasar_pengenaan' => $calc['dasar_pengenaan'],
                'jumlah_pajak' => $calc['jumlah_pajak'],
                'status' => 'draft',
                'tanggal_buat' => now(),
                'petugas_id' => $data['petugas_id'],
                'petugas_nama' => $data['petugas_nama'],
                'dasar_hukum' => $data['dasar_hukum'] ?? null,
            ]);

            // Update last_meter_reading on tax object
            TaxObject::where('id', $data['tax_object_id'])->update([
                'last_meter_reading' => $data['meter_reading_after'],
                'last_report_date' => now(),
            ]);

            return $skpd;
        });
    }
}
