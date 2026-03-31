<?php

namespace App\Domain\Tax\Services;

use App\Domain\Master\Models\JenisPajak;
use App\Enums\TaxStatus;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TarifPajak;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\WajibPajak\Models\WajibPajak;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Calculate tax amount from omzet and tariff percentage.
     */
    public function calculateTax(float $omzet, float $tarifPersen): float
    {
        return $omzet * ($tarifPersen / 100);
    }

    /**
     * Determine the next masa pajak for a given tax object.
     * Returns array with keys: bulan, tahun, label, isNew.
     */
    public function getNextMasaPajak(string $taxObjectId): array
    {
        $taxObject = $this->resolveTaxObject($taxObjectId);

        if (!$taxObject) {
            return $this->currentMonthlyPeriod(isNew: true);
        }

        $isMultiBilling = $taxObject->isMultiBilling();
        $isMblbWapu = $taxObject->subJenisPajak?->kode === 'MBLB_WAPU';
        $shouldUseCurrentPeriod = $isMblbWapu || (bool) $taxObject->is_opd || (bool) $taxObject->is_insidentil;

        // Sarang Walet: masa pajak tahunan
        $isSarangWalet = $taxObject->jenisPajak?->kode === '41109';

        if ($isSarangWalet) {
            $lastTax = Tax::where('tax_object_id', $taxObjectId)
                ->whereIn('status', TaxStatus::activeStatuses())
                ->orderByDesc('masa_pajak_tahun')
                ->first();

            if ($lastTax && $lastTax->masa_pajak_tahun) {
                $tahun = (int) $lastTax->masa_pajak_tahun + 1;

                return [
                    'bulan' => null,
                    'tahun' => $tahun,
                    'label' => 'Tahun ' . $tahun,
                    'isNew' => false,
                    'isMultiBilling' => false,
                    'isYearly' => true,
                ];
            }

            $now = Carbon::now();

            return [
                'bulan' => null,
                'tahun' => (int) $now->year,
                'label' => 'Tahun ' . $now->year,
                'isNew' => true,
                'isMultiBilling' => false,
                'isYearly' => true,
            ];
        }

        if ($shouldUseCurrentPeriod) {
            return $this->currentMonthlyPeriod(isNew: false, isMultiBilling: true);
        }

        $lastTax = $this->findLastActiveTaxByNopd($taxObject);

        if ($lastTax && $lastTax->masa_pajak_tahun) {
            $bulan = (int) ($lastTax->masa_pajak_bulan ?: 1);
            $tahun = (int) $lastTax->masa_pajak_tahun;

            if ($bulan >= 12) {
                $bulan = 1;
                $tahun++;
            } else {
                $bulan++;
            }

            return [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'label' => Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y'),
                'isNew' => false,
                'isMultiBilling' => false,
                'isYearly' => false,
            ];
        }

        return $this->currentMonthlyPeriod(isNew: true);
    }

    private function findLastActiveTaxByNopd(TaxObject $taxObject): ?Tax
    {
        if (!$taxObject->nopd) {
            return null;
        }

        return Tax::whereIn('status', TaxStatus::activeStatuses())
            ->whereHas('taxObject', function ($query) use ($taxObject) {
                $query->where('nopd', $taxObject->nopd)
                    ->where('jenis_pajak_id', $taxObject->jenis_pajak_id);
            })
            ->orderByRaw('masa_pajak_tahun DESC, masa_pajak_bulan DESC')
            ->first();
    }

    public function findExistingBillingForPeriod(string $taxObjectId, int $bulan, int $tahun): ?Tax
    {
        $taxObject = $this->resolveTaxObject($taxObjectId);

        if (!$taxObject) {
            return null;
        }

        $query = Tax::where('masa_pajak_bulan', $bulan)
            ->where('masa_pajak_tahun', $tahun)
            ->whereIn('status', TaxStatus::activeStatuses())
            ->orderByDesc('pembetulan_ke');

        if ($taxObject->nopd) {
            $query->whereHas('taxObject', function ($builder) use ($taxObject) {
                $builder->where('nopd', $taxObject->nopd)
                    ->where('jenis_pajak_id', $taxObject->jenis_pajak_id);
            });
        } else {
            $query->where('tax_object_id', $taxObject->id);
        }

        return $query->first();
    }

    public function billingExistsForPeriod(string $taxObjectId, int $bulan, int $tahun): bool
    {
        return $this->findExistingBillingForPeriod($taxObjectId, $bulan, $tahun) !== null;
    }

    public function getNextBillingSequence(string $taxObjectId, int $bulan, int $tahun): int
    {
        $taxObject = $this->resolveTaxObject($taxObjectId);

        if (!$taxObject) {
            return 1;
        }

        $query = Tax::where('masa_pajak_bulan', $bulan)
            ->where('masa_pajak_tahun', $tahun)
            ->whereIn('status', TaxStatus::activeStatuses());

        if ($taxObject->nopd) {
            $query->whereHas('taxObject', function ($builder) use ($taxObject) {
                $builder->where('nopd', $taxObject->nopd)
                    ->where('jenis_pajak_id', $taxObject->jenis_pajak_id);
            });
        } else {
            $query->where('tax_object_id', $taxObject->id);
        }

        return (int) $query->max('billing_sequence') + 1;
    }

    private function currentMonthlyPeriod(bool $isNew, bool $isMultiBilling = false): array
    {
        $now = Carbon::now();

        return [
            'bulan' => (int) $now->month,
            'tahun' => (int) $now->year,
            'label' => $now->translatedFormat('F Y'),
            'isNew' => $isNew,
            'isMultiBilling' => $isMultiBilling,
            'isYearly' => false,
        ];
    }

    private function resolveTaxObject(string $taxObjectId): ?TaxObject
    {
        return TaxObject::with(['jenisPajak', 'subJenisPajak'])->find($taxObjectId);
    }

    /**
     * Generate a billing (Tax record) for a self-assessment submission.
     * Used by portal (WP) — billing expires in 7 days.
     */
    public function generateBillingForPortal(array $data): Tax
    {
        return DB::transaction(function () use ($data) {
            $taxObject = TaxObject::with(['jenisPajak', 'subJenisPajak'])
                ->findOrFail($data['tax_object_id']);

            // Lookup tarif dari tabel tarif_pajak berdasarkan tanggal masa pajak
            $tanggalMasaPajak = Carbon::create($data['tahun'], $data['bulan'], 1)->toDateString();
            $tarifInfo = TarifPajak::lookupWithDasarHukum($taxObject->sub_jenis_pajak_id, $tanggalMasaPajak);

            $tarifPersen = $tarifInfo['tarif_persen']
                ?? $taxObject->tarif_persen
                ?? $taxObject->jenisPajak->tarif_default
                ?? 10;
            $dasarHukum = $tarifInfo['dasar_hukum'] ?? null;

            $taxAmount = $this->calculateTax($data['omzet'], $tarifPersen);
            $billingCode = Tax::generateBillingCode($taxObject->jenisPajak->kode);

            return Tax::create([
                'jenis_pajak_id' => $taxObject->jenis_pajak_id,
                'sub_jenis_pajak_id' => $taxObject->sub_jenis_pajak_id,
                'tax_object_id' => $taxObject->id,
                'user_id' => $data['user_id'],
                'amount' => $taxAmount,
                'omzet' => $data['omzet'],
                'tarif_persentase' => $tarifPersen,
                'status' => TaxStatus::Pending->value,
                'billing_code' => $billingCode,
                'attachment_url' => $data['attachment_url'] ?? null,
                'payment_expired_at' => Tax::hitungJatuhTempoSelfAssessment($data['bulan'], $data['tahun']),
                'masa_pajak_bulan' => $data['bulan'],
                'masa_pajak_tahun' => $data['tahun'],
                'billing_sequence' => $data['billing_sequence'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'dasar_hukum' => $dasarHukum,
            ]);
        });
    }

    /**
     * Generate a billing (Tax record) by petugas (officer).
     * Billing expires in 1 month.
     */
    public function generateBillingByPetugas(array $data): Tax
    {
        return DB::transaction(function () use ($data) {
            $jenisPajak = JenisPajak::find($data['jenis_pajak_id']);
            $billingCode = Tax::generateBillingCode($jenisPajak->kode ?? '41102');

            // Lookup tarif dari tabel tarif_pajak jika tidak di-override dari caller
            $tarifPersen = $data['tarif_persen'];
            $dasarHukum = $data['dasar_hukum'] ?? null;

            if (!$dasarHukum && !empty($data['sub_jenis_pajak_id'])) {
                $tanggalMasaPajak = Carbon::create($data['tahun'], $data['bulan'], 1)->toDateString();
                $tarifInfo = TarifPajak::lookupWithDasarHukum($data['sub_jenis_pajak_id'], $tanggalMasaPajak);
                $dasarHukum = $tarifInfo['dasar_hukum'] ?? null;
            }

            $taxAmount = $this->calculateTax($data['omzet'], $tarifPersen);

            $attributes = [
                'jenis_pajak_id' => $data['jenis_pajak_id'],
                'sub_jenis_pajak_id' => $data['sub_jenis_pajak_id'],
                'tax_object_id' => $data['tax_object_id'],
                'user_id' => $data['user_id'],
                'amount' => $taxAmount,
                'omzet' => $data['omzet'],
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
                'dasar_hukum' => $dasarHukum,
            ];

            if (isset($data['opsen'])) {
                $attributes['opsen'] = (string) $data['opsen'];
            }

            return Tax::create($attributes);
        });
    }

    /**
     * Prepare billing document data for viewing or PDF generation.
     */
    public function getBillingDocumentData(string $taxId): array
    {
        $tax = Tax::with(['jenisPajak', 'subJenisPajak', 'taxObject', 'user'])
            ->findOrFail($taxId);

        $taxObject = $tax->taxObject;
        $wajibPajak = WajibPajak::where('user_id', $tax->user_id)->first();

        if (!$wajibPajak) {
            abort(404, 'Data Wajib Pajak tidak ditemukan');
        }

        return compact('tax', 'taxObject', 'wajibPajak');
    }

    /**
     * Get formatted period label.
     */
    public function getPeriodLabel(int $bulan, int $tahun): string
    {
        return Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y');
    }
}
