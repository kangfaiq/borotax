<?php

namespace App\Domain\Tax\Services;

use App\Domain\Master\Models\JenisPajak;
use App\Enums\TaxStatus;
use App\Domain\Tax\Models\HargaPatokanSarangWalet;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxSarangWaletDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SarangWaletService
{
    /**
     * Calculate Sarang Walet tax from a single item.
     *
     * @param float $hargaPatokan Harga pasaran umum per kg
     * @param float $volumeKg Volume dalam kg
     * @param float $tarifPersen Tax rate percentage (e.g. 10)
     * @return array ['dpp', 'pokok_pajak', 'total']
     */
    public function calculateTax(float $hargaPatokan, float $volumeKg, float $tarifPersen): array
    {
        $dpp = round($hargaPatokan * $volumeKg);
        $pokokPajak = round($dpp * $tarifPersen / 100);

        return [
            'dpp' => $dpp,
            'pokok_pajak' => $pokokPajak,
            'total' => $pokokPajak,
        ];
    }

    /**
     * Generate Sarang Walet billing with detail record.
     */
    public function generateBilling(array $data): Tax
    {
        return DB::transaction(function () use ($data) {
            $jenisPajak = JenisPajak::find($data['jenis_pajak_id']);
            $billingCode = Tax::generateBillingCode($jenisPajak->kode ?? '41109');

            // Sarang Walet: masa pajak tahunan, no jatuh tempo
            // payment_expired_at = 7 hari dari sekarang (masa berlaku billing)
            $paymentExpiredAt = now()->addDays(7)->endOfDay();

            $tax = Tax::create([
                'jenis_pajak_id' => $data['jenis_pajak_id'],
                'sub_jenis_pajak_id' => $data['sub_jenis_pajak_id'] ?? null,
                'tax_object_id' => $data['tax_object_id'],
                'user_id' => $data['user_id'],
                'amount' => (string) $data['pokok_pajak'],
                'omzet' => (string) $data['dpp'],
                'tarif_persentase' => $data['tarif_persen'],
                'status' => TaxStatus::Pending->value,
                'billing_code' => $billingCode,
                'payment_expired_at' => $paymentExpiredAt,
                'masa_pajak_bulan' => $data['bulan'] ?? null,
                'masa_pajak_tahun' => $data['tahun'],
                'pembetulan_ke' => $data['pembetulan_ke'] ?? 0,
                'revision_attempt_no' => $data['revision_attempt_no'] ?? 0,
                'billing_sequence' => $data['billing_sequence'] ?? 0,
                'parent_tax_id' => $data['parent_tax_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'attachment_url' => $data['attachment_url'] ?? null,
                'dasar_hukum' => $data['dasar_hukum'] ?? null,
            ]);

            // Create detail record (single item per billing)
            TaxSarangWaletDetail::create([
                'tax_id' => $tax->id,
                'harga_patokan_sarang_walet_id' => $data['harga_patokan_sarang_walet_id'],
                'jenis_sarang' => $data['jenis_sarang'],
                'volume_kg' => $data['volume_kg'],
                'harga_patokan' => (string) $data['harga_patokan'],
                'subtotal_dpp' => (string) $data['dpp'],
            ]);

            return $tax;
        });
    }

    /**
     * Generate billing by petugas (officer) — billing expires in 1 month.
     */
    public function generateBillingByPetugas(array $data): Tax
    {
        $data['notes'] = $data['notes'] ?? ('Dibuat oleh petugas: ' . (auth()->user()->nama_lengkap ?? auth()->user()->name));
        $result = $this->generateBilling($data);

        // Override payment_expired_at to 1 month for petugas-created billing
        $result->update([
            'payment_expired_at' => now()->addMonth()->endOfDay(),
        ]);

        return $result;
    }

    /**
     * Get all active jenis sarang items for the billing form.
     */
    public function getAllJenisSarang(?Carbon $tanggal = null): Collection
    {
        $query = HargaPatokanSarangWalet::active();

        if ($tanggal) {
            $query->berlakuPada($tanggal->toDateString());
        }

        return $query->orderBy('nama_jenis')->get();
    }
}
