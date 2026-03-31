<?php

namespace App\Domain\Tax\Observers;

use App\Domain\Tax\Models\Tax;
use App\Enums\TaxStatus;

class TaxObserver
{
    /**
     * Handle the Tax "updated" event.
     */
    public function updated(Tax $tax): void
    {
        if (!$tax->isDirty('status')) {
            return;
        }

        // Status changed to paid: SPTPD + STPD
        if ($tax->status === TaxStatus::Paid) {
            $updates = false;

            // SPTPD: Untuk PBJT reguler, hanya diterbitkan jika triwulan lengkap terbayar.
            // Untuk insidentil/OPD/non-PBJT, langsung diterbitkan.
            if (empty($tax->sptpd_number) && $tax->isTriwulanComplete()) {
                $tax->sptpd_number = $tax->billing_code;
                $updates = true;

                // Backfill: set sptpd_number untuk semua billing saudara di triwulan yang sama
                $this->backfillTriwulanSptpd($tax);
            }

            // STPD: via checkAndIssueStpd() — terbit jika pokok lunas,
            // ada sanksi, dan triwulan yang relevan sudah lengkap.
            if (empty($tax->stpd_number)) {
                if ($tax->checkAndIssueStpd()) {
                    $tax->refresh(); // stpd_number sudah di-set oleh checkAndIssueStpd
                    // Backfill: set stpd_number untuk billing saudara yang punya sanksi
                    $this->backfillTriwulanStpd($tax);
                }
            }

            if ($updates) {
                $tax->saveQuietly();
            }
        }

        // Status changed to partially_paid: cek STPD jika pokok mungkin sudah lunas
        // dan triwulan terkait ternyata sudah lengkap.
        if ($tax->status === TaxStatus::PartiallyPaid) {
            $tax->checkAndIssueStpd();
        }
    }

    /**
     * Backfill sptpd_number ke billing saudara (objek yg sama) di triwulan yang sama
     * yang sudah terbayar tapi belum punya sptpd_number.
     */
    private function backfillTriwulanSptpd(Tax $tax): void
    {
        if (!$tax->isSelfAssessmentPbjt() || $tax->isMultiBilling()) {
            return;
        }

        $triwulanMonths = Tax::getTriwulanRange((int) $tax->masa_pajak_bulan);

        $siblings = Tax::where('tax_object_id', $tax->tax_object_id)
            ->where('masa_pajak_tahun', $tax->masa_pajak_tahun)
            ->whereIn('masa_pajak_bulan', $triwulanMonths)
            ->where('pembetulan_ke', 0)
            ->where('billing_sequence', 0)
            ->whereIn('status', [TaxStatus::Paid])
            ->whereNull('sptpd_number')
            ->where('id', '!=', $tax->id)
            ->get();

        foreach ($siblings as $sibling) {
            $sibling->sptpd_number = $sibling->billing_code;
            $sibling->saveQuietly();
        }
    }

    /**
     * Backfill stpd_number ke billing saudara di triwulan yang sama
     * yang sudah terbayar, punya sanksi, tapi belum punya stpd_number.
     */
    private function backfillTriwulanStpd(Tax $tax): void
    {
        if (!$tax->isSelfAssessmentPbjt() || $tax->isMultiBilling()) {
            return;
        }

        $triwulanMonths = Tax::getTriwulanRange((int) $tax->masa_pajak_bulan);

        $siblings = Tax::where('tax_object_id', $tax->tax_object_id)
            ->where('masa_pajak_tahun', $tax->masa_pajak_tahun)
            ->whereIn('masa_pajak_bulan', $triwulanMonths)
            ->where('pembetulan_ke', 0)
            ->where('billing_sequence', 0)
            ->whereIn('status', [TaxStatus::Paid])
            ->whereNull('stpd_number')
            ->where('id', '!=', $tax->id)
            ->get();

        foreach ($siblings as $sibling) {
            $sanksi = (float) $sibling->sanksi;
            if ($sanksi > 0) {
                $sibling->stpd_number = $sibling->billing_code;
                $sibling->saveQuietly();
            }
        }
    }
}
