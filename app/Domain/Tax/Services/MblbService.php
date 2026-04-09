<?php

namespace App\Domain\Tax\Services;

use App\Domain\Master\Models\JenisPajak;
use App\Enums\TaxStatus;
use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxMblbDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MblbService
{
    /**
     * Calculate MBLB tax from mineral items.
     *
     * @param array $items Array of ['harga_patokan_mblb_id', 'jenis_mblb', 'volume', 'harga_patokan']
     * @param float $tarifPersen Tax rate percentage (e.g. 20)
     * @param float $opsenPersen Opsen rate percentage (e.g. 25)
     * @return array ['total_dpp', 'pokok_pajak', 'opsen', 'total', 'details']
     */
    public function calculateTax(array $items, float $tarifPersen, float $opsenPersen): array
    {
        $details = [];
        $totalDpp = 0;

        foreach ($items as $item) {
            $volume = (float) ($item['volume'] ?? 0);
            if ($volume <= 0) {
                continue;
            }

            $hargaPatokan = (float) ($item['harga_patokan'] ?? 0);
            $subtotalDpp = round($volume * $hargaPatokan);

            $details[] = [
                'harga_patokan_mblb_id' => $item['harga_patokan_mblb_id'] ?? null,
                'jenis_mblb' => $item['jenis_mblb'] ?? '',
                'volume' => $volume,
                'harga_patokan' => $hargaPatokan,
                'subtotal_dpp' => $subtotalDpp,
            ];

            $totalDpp += $subtotalDpp;
        }

        $pokokPajak = round($totalDpp * $tarifPersen / 100);
        $opsen = round($pokokPajak * $opsenPersen / 100);

        return [
            'total_dpp' => $totalDpp,
            'pokok_pajak' => $pokokPajak,
            'opsen' => $opsen,
            'total' => $pokokPajak + $opsen,
            'details' => $details,
        ];
    }

    /**
     * Generate MBLB billing with detail records.
     */
    public function generateBilling(array $data): Tax
    {
        return DB::transaction(function () use ($data) {
            $jenisPajak = JenisPajak::find($data['jenis_pajak_id']);
            $billingCode = Tax::generateBillingCode($jenisPajak->kode ?? '41106');

            $tax = Tax::create([
                'jenis_pajak_id' => $data['jenis_pajak_id'],
                'sub_jenis_pajak_id' => $data['sub_jenis_pajak_id'] ?? null,
                'tax_object_id' => $data['tax_object_id'],
                'user_id' => $data['user_id'],
                'amount' => (string) $data['pokok_pajak'],
                'omzet' => (string) $data['total_dpp'],
                'opsen' => (string) $data['opsen'],
                'tarif_persentase' => $data['tarif_persen'],
                'status' => TaxStatus::Pending->value,
                'billing_code' => $billingCode,
                'payment_expired_at' => Tax::hitungJatuhTempoSelfAssessment($data['bulan'], $data['tahun']),
                'masa_pajak_bulan' => $data['bulan'],
                'masa_pajak_tahun' => $data['tahun'],
                'pembetulan_ke' => $data['pembetulan_ke'] ?? 0,
                'revision_attempt_no' => $data['revision_attempt_no'] ?? 0,
                'billing_sequence' => $data['billing_sequence'] ?? 0,
                'parent_tax_id' => $data['parent_tax_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'attachment_url' => $data['attachment_url'] ?? null,
            ]);

            // Create detail records (only items with volume > 0, already filtered by calculateTax)
            foreach ($data['details'] as $detail) {
                TaxMblbDetail::create([
                    'tax_id' => $tax->id,
                    'harga_patokan_mblb_id' => $detail['harga_patokan_mblb_id'],
                    'jenis_mblb' => $detail['jenis_mblb'],
                    'volume' => $detail['volume'],
                    'harga_patokan' => (string) $detail['harga_patokan'],
                    'subtotal_dpp' => (string) $detail['subtotal_dpp'],
                ]);
            }

            return $tax;
        });
    }

    /**
     * Get all active mineral items for the billing form.
     */
    public function getAllMineralItems(): Collection
    {
        return HargaPatokanMblb::active()
            ->orderBy('nama_mineral')
            ->get();
    }
}
