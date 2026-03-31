<?php

namespace App\Domain\Tax\Services;

use App\Domain\Master\Models\JenisPajak;
use App\Enums\TaxStatus;
use App\Domain\Tax\Models\HargaSatuanListrik;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TarifPajak;
use App\Domain\Tax\Models\TaxPpjDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PpjService
{
    /**
     * Calculate Nilai Jual Tenaga Listrik (NJTL) for Non-PLN.
     *
     * NJTL = kapasitas_kva × (tingkat_penggunaan / 100) × jangka_waktu_jam × harga_satuan_per_kwh
     */
    public function calculateNjtl(
        float $kapasitasKva,
        float $tingkatPenggunaanPersen,
        float $jangkaWaktuJam,
        float $hargaSatuanPerKwh
    ): float {
        return round($kapasitasKva * ($tingkatPenggunaanPersen / 100) * $jangkaWaktuJam * $hargaSatuanPerKwh);
    }

    /**
     * Calculate tax amount from DPP and tariff percentage.
     */
    public function calculateTax(float $dpp, float $tarifPersen): float
    {
        return round($dpp * ($tarifPersen / 100));
    }

    /**
     * Generate billing for PPJ Sumber Lain (PLN).
     * Input: pokok pajak terutang (amount) langsung.
     * DPP di-back-calculate: DPP = pokok_pajak / (tarif / 100).
     */
    public function generateBillingPpjSumberLain(array $data): Tax
    {
        return DB::transaction(function () use ($data) {
            $jenisPajak = JenisPajak::find($data['jenis_pajak_id']);
            $billingCode = Tax::generateBillingCode($jenisPajak->kode ?? '41105');

            $pokokPajak = (float) $data['pokok_pajak'];
            $tarifPersen = (float) $data['tarif_persen'];
            $dpp = $tarifPersen > 0 ? round($pokokPajak / ($tarifPersen / 100)) : 0;

            // Lookup dasar hukum
            $dasarHukum = $data['dasar_hukum'] ?? null;
            if (!$dasarHukum && !empty($data['sub_jenis_pajak_id'])) {
                $tanggalMasaPajak = Carbon::create($data['tahun'], $data['bulan'], 1)->toDateString();
                $tarifInfo = TarifPajak::lookupWithDasarHukum($data['sub_jenis_pajak_id'], $tanggalMasaPajak);
                $dasarHukum = $tarifInfo['dasar_hukum'] ?? null;
            }

            return Tax::create([
                'jenis_pajak_id' => $data['jenis_pajak_id'],
                'sub_jenis_pajak_id' => $data['sub_jenis_pajak_id'] ?? null,
                'tax_object_id' => $data['tax_object_id'],
                'user_id' => $data['user_id'],
                'amount' => (string) $pokokPajak,
                'omzet' => (string) $dpp,
                'tarif_persentase' => $tarifPersen,
                'status' => TaxStatus::Pending->value,
                'billing_code' => $billingCode,
                'payment_expired_at' => Tax::hitungJatuhTempoSelfAssessment($data['bulan'], $data['tahun']),
                'masa_pajak_bulan' => $data['bulan'],
                'masa_pajak_tahun' => $data['tahun'],
                'pembetulan_ke' => $data['pembetulan_ke'] ?? 0,
                'billing_sequence' => $data['billing_sequence'] ?? 0,
                'parent_tax_id' => $data['parent_tax_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'attachment_url' => $data['attachment_url'] ?? null,
                'dasar_hukum' => $dasarHukum,
            ]);
        });
    }

    /**
     * Generate billing for PPJ Dihasilkan Sendiri (Non PLN).
     * Hitung NJTL dari 4 komponen, simpan detail di tax_ppj_details.
     */
    public function generateBillingPpjNonPln(array $data): Tax
    {
        return DB::transaction(function () use ($data) {
            $jenisPajak = JenisPajak::find($data['jenis_pajak_id']);
            $billingCode = Tax::generateBillingCode($jenisPajak->kode ?? '41105');

            $kapasitasKva = (float) $data['kapasitas_kva'];
            $tingkatPenggunaan = (float) $data['tingkat_penggunaan_persen'];
            $jangkaWaktu = (float) $data['jangka_waktu_jam'];
            $hargaSatuan = (float) $data['harga_satuan'];
            $tarifPersen = (float) $data['tarif_persen'];

            $njtl = $this->calculateNjtl($kapasitasKva, $tingkatPenggunaan, $jangkaWaktu, $hargaSatuan);
            $dpp = $njtl;
            $pokokPajak = $this->calculateTax($dpp, $tarifPersen);

            // Lookup dasar hukum
            $dasarHukum = $data['dasar_hukum'] ?? null;
            if (!$dasarHukum && !empty($data['sub_jenis_pajak_id'])) {
                $tanggalMasaPajak = Carbon::create($data['tahun'], $data['bulan'], 1)->toDateString();
                $tarifInfo = TarifPajak::lookupWithDasarHukum($data['sub_jenis_pajak_id'], $tanggalMasaPajak);
                $dasarHukum = $tarifInfo['dasar_hukum'] ?? null;
            }

            $tax = Tax::create([
                'jenis_pajak_id' => $data['jenis_pajak_id'],
                'sub_jenis_pajak_id' => $data['sub_jenis_pajak_id'] ?? null,
                'tax_object_id' => $data['tax_object_id'],
                'user_id' => $data['user_id'],
                'amount' => (string) $pokokPajak,
                'omzet' => (string) $dpp,
                'tarif_persentase' => $tarifPersen,
                'status' => TaxStatus::Pending->value,
                'billing_code' => $billingCode,
                'payment_expired_at' => Tax::hitungJatuhTempoSelfAssessment($data['bulan'], $data['tahun']),
                'masa_pajak_bulan' => $data['bulan'],
                'masa_pajak_tahun' => $data['tahun'],
                'pembetulan_ke' => $data['pembetulan_ke'] ?? 0,
                'billing_sequence' => $data['billing_sequence'] ?? 0,
                'parent_tax_id' => $data['parent_tax_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'attachment_url' => $data['attachment_url'] ?? null,
                'dasar_hukum' => $dasarHukum,
            ]);

            // Create detail record
            TaxPpjDetail::create([
                'tax_id' => $tax->id,
                'harga_satuan_listrik_id' => $data['harga_satuan_listrik_id'] ?? null,
                'kapasitas_kva' => $kapasitasKva,
                'tingkat_penggunaan_persen' => $tingkatPenggunaan,
                'jangka_waktu_jam' => $jangkaWaktu,
                'harga_satuan' => (string) $hargaSatuan,
                'njtl' => (string) $njtl,
                'subtotal_dpp' => (string) $dpp,
            ]);

            return $tax;
        });
    }

    /**
     * Get all active harga satuan listrik items for the billing form.
     */
    public function getAllHargaSatuan(?Carbon $tanggal = null): Collection
    {
        $query = HargaSatuanListrik::active();

        if ($tanggal) {
            $query->berlakuPada($tanggal->toDateString());
        }

        return $query->orderBy('nama_wilayah')->get();
    }
}
