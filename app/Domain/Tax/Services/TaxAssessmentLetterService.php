<?php

namespace App\Domain\Tax\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Shared\Services\NotificationService;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxAssessmentCompensation;
use App\Domain\Tax\Models\TaxAssessmentLetter;
use App\Domain\Tax\Models\TaxPayment;
use App\Domain\Tax\Services\BillingService;
use App\Enums\TaxAssessmentLetterStatus;
use App\Enums\TaxAssessmentLetterType;
use App\Enums\TaxAssessmentReason;
use App\Enums\TaxStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TaxAssessmentLetterService
{
    public function prepareDraftPayload(array $data, User $creator): array
    {
        $sourceTax = Tax::with(['jenisPajak', 'taxObject', 'user'])->findOrFail($data['source_tax_id']);
        $letterType = $data['letter_type'] instanceof TaxAssessmentLetterType
            ? $data['letter_type']
            : TaxAssessmentLetterType::from($data['letter_type']);
        $issuanceReason = $data['issuance_reason'] instanceof TaxAssessmentReason
            ? $data['issuance_reason']
            : TaxAssessmentReason::from($data['issuance_reason']);
        $issueDate = Carbon::parse($data['issue_date'] ?? now());

        $this->assertReasonMatchesLetterType($letterType, $issuanceReason);

        $baseAmount = $letterType === TaxAssessmentLetterType::SKPDN
            ? 0.0
            : (float) ($data['base_amount'] ?? 0);

        if ($letterType !== TaxAssessmentLetterType::SKPDN && $baseAmount <= 0) {
            throw new InvalidArgumentException('Nominal dasar ketetapan harus lebih besar dari 0.');
        }

        $interestMonths = (int) ($data['interest_months'] ?? $this->resolveInterestMonths($sourceTax, $issueDate, $letterType));
        if ($interestMonths < 0) {
            throw new InvalidArgumentException('Jumlah bulan bunga tidak boleh negatif.');
        }

        $interestRate = $this->resolveInterestRate($letterType, $issuanceReason);
        $surchargeRate = $this->resolveSurchargeRate($sourceTax, $letterType, $issuanceReason);

        $interestAmount = round($baseAmount * $interestRate * $interestMonths, 2);
        $surchargeAmount = round($baseAmount * $surchargeRate, 2);
        $totalAssessment = match ($letterType) {
            TaxAssessmentLetterType::SKPDN => 0.0,
            TaxAssessmentLetterType::SKPDLB => round($baseAmount, 2),
            default => round($baseAmount + $interestAmount + $surchargeAmount, 2),
        };

        $dueDate = $letterType->allowsGeneratedBilling()
            ? $issueDate->copy()->addMonth()
            : null;

        return [
            'source_tax_id' => $sourceTax->id,
            'parent_letter_id' => $data['parent_letter_id'] ?? null,
            'user_id' => $sourceTax->user_id,
            'tax_object_id' => $sourceTax->tax_object_id,
            'letter_type' => $letterType,
            'issuance_reason' => $issuanceReason,
            'status' => TaxAssessmentLetterStatus::Draft,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'base_amount' => $baseAmount,
            'interest_rate' => round($interestRate * 100, 2),
            'interest_months' => $interestMonths,
            'interest_amount' => $interestAmount,
            'surcharge_rate' => round($surchargeRate * 100, 2),
            'surcharge_amount' => $surchargeAmount,
            'total_assessment' => $totalAssessment,
            'available_credit' => $letterType === TaxAssessmentLetterType::SKPDLB ? $totalAssessment : 0,
            'notes' => $data['notes'] ?? null,
            'created_by' => $creator->id,
            'created_by_name' => $creator->nama_lengkap ?? $creator->name,
        ];
    }

    public function approve(TaxAssessmentLetter $letter, User $verifier, ?string $verificationNotes = null): TaxAssessmentLetter
    {
        if (!$letter->isDraft()) {
            throw new InvalidArgumentException('Hanya draft yang dapat diterbitkan.');
        }

        if ($letter->created_by === $verifier->id) {
            throw new InvalidArgumentException('Dokumen tidak dapat diverifikasi oleh pembuat draft yang sama.');
        }

        return DB::transaction(function () use ($letter, $verifier, $verificationNotes) {
            $letter->loadMissing(['sourceTax.jenisPajak', 'sourceTax.taxObject', 'user']);

            $pimpinan = $verifier->pimpinan_id
                ? Pimpinan::find($verifier->pimpinan_id)
                : Pimpinan::whereNull('bidang')->whereNull('sub_bidang')->first();

            $letter->fill([
                'status' => TaxAssessmentLetterStatus::Disetujui,
                'document_number' => $letter->document_number ?: $this->generateDocumentNumber($letter->letter_type),
                'verification_notes' => $verificationNotes,
                'verified_by' => $verifier->id,
                'verified_by_name' => $verifier->nama_lengkap ?? $verifier->name,
                'verified_at' => now(),
                'pimpinan_id' => $pimpinan?->id,
            ]);

            if ($letter->letter_type->allowsGeneratedBilling() && !$letter->generated_tax_id) {
                $generatedTax = $this->createGeneratedTax($letter);
                $letter->generated_tax_id = $generatedTax->id;
            }

            if ($letter->letter_type === TaxAssessmentLetterType::SKPDLB) {
                $letter->available_credit = (float) $letter->total_assessment;
            }

            $letter->save();

            if ($letter->user) {
                NotificationService::notifyUserBoth(
                    $letter->user,
                    'Surat Ketetapan Pajak Diterbitkan',
                    'Dokumen ' . $letter->letter_type->getLabel() . ' nomor ' . $letter->document_number . ' telah diterbitkan.',
                    'verification'
                );
            }

            return $letter;
        });
    }

    public function reject(TaxAssessmentLetter $letter, User $verifier, string $verificationNotes): TaxAssessmentLetter
    {
        if (!$letter->isDraft()) {
            throw new InvalidArgumentException('Hanya draft yang dapat ditolak.');
        }

        if ($letter->created_by === $verifier->id) {
            throw new InvalidArgumentException('Dokumen tidak dapat diverifikasi oleh pembuat draft yang sama.');
        }

        $letter->update([
            'status' => TaxAssessmentLetterStatus::Ditolak,
            'verification_notes' => $verificationNotes,
            'verified_by' => $verifier->id,
            'verified_by_name' => $verifier->nama_lengkap ?? $verifier->name,
            'verified_at' => now(),
        ]);

        if ($letter->user) {
            NotificationService::notifyUserBoth(
                $letter->user,
                'Surat Ketetapan Pajak Ditolak',
                'Draft ' . $letter->letter_type->getLabel() . ' ditolak. Alasan: ' . $verificationNotes,
                'verification'
            );
        }

        return $letter;
    }

    public function allocateCredit(TaxAssessmentLetter $letter, Tax $targetTax, float $allocationAmount, User $allocator): TaxAssessmentCompensation
    {
        if (!$letter->allowsCompensation() || !$letter->isApproved()) {
            throw new InvalidArgumentException('Dokumen ini tidak dapat digunakan untuk kompensasi.');
        }

        if ($allocationAmount <= 0) {
            throw new InvalidArgumentException('Nominal kompensasi harus lebih besar dari 0.');
        }

        if ($letter->user_id && $targetTax->user_id && $letter->user_id !== $targetTax->user_id) {
            throw new InvalidArgumentException('Kompensasi hanya dapat dialokasikan ke tagihan wajib pajak yang sama.');
        }

        return DB::transaction(function () use ($letter, $targetTax, $allocationAmount, $allocator) {
            $letter->refresh();
            $availableCredit = (float) ($letter->available_credit ?? 0);
            $remainingTarget = $targetTax->getRemainingAmount();

            if ($availableCredit <= 0) {
                throw new InvalidArgumentException('Saldo kredit tidak tersedia.');
            }

            if ($allocationAmount > $availableCredit) {
                throw new InvalidArgumentException('Nominal kompensasi melebihi saldo kredit yang tersedia.');
            }

            if ($allocationAmount > $remainingTarget) {
                throw new InvalidArgumentException('Nominal kompensasi melebihi sisa tagihan target.');
            }

            $principalRemaining = max(0, (float) $targetTax->amount - $targetTax->getTotalPrincipalPaid());
            $penaltyRemaining = max(0, (float) $targetTax->sanksi - $targetTax->getTotalPenaltyPaid());
            $principalAllocated = min($principalRemaining, $allocationAmount);
            $penaltyAllocated = min($penaltyRemaining, max(0, $allocationAmount - $principalAllocated));

            $payment = TaxPayment::create([
                'tax_id' => $targetTax->id,
                'external_ref' => 'KOMP-' . ($letter->document_number ?? $letter->id) . '-' . time(),
                'amount_paid' => $allocationAmount,
                'principal_paid' => $principalAllocated,
                'penalty_paid' => $penaltyAllocated,
                'payment_channel' => 'KOMPENSASI_SKPDLB',
                'paid_at' => now(),
                'description' => 'Kompensasi dari ' . ($letter->document_number ?? $letter->id),
                'raw_response' => ['source_letter_id' => $letter->id],
            ]);

            $newTotalPaid = $targetTax->fresh()->getTotalPaid();
            $targetTotal = (float) $targetTax->amount + (float) $targetTax->sanksi;

            if ($newTotalPaid >= $targetTotal) {
                $targetTax->update([
                    'status' => TaxStatus::Paid,
                    'paid_at' => now(),
                    'payment_channel' => 'KOMPENSASI_SKPDLB',
                    'payment_ref' => $letter->document_number,
                ]);
            } else {
                $targetTax->update([
                    'status' => TaxStatus::PartiallyPaid,
                ]);
            }

            $compensation = TaxAssessmentCompensation::create([
                'tax_assessment_letter_id' => $letter->id,
                'target_tax_id' => $targetTax->id,
                'tax_payment_id' => $payment->id,
                'allocation_amount' => $allocationAmount,
                'principal_allocated' => $principalAllocated,
                'penalty_allocated' => $penaltyAllocated,
                'allocated_at' => now(),
                'allocated_by' => $allocator->id,
                'allocated_by_name' => $allocator->nama_lengkap ?? $allocator->name,
            ]);

            $letter->refreshAvailableCredit();

            if ($letter->user) {
                NotificationService::notifyUserBoth(
                    $letter->user,
                    'Kompensasi SKPDLB Diproses',
                    'Saldo kredit ' . ($letter->document_number ?? $letter->id) . ' telah dialokasikan ke billing ' . $targetTax->billing_code . '.',
                    'payment'
                );
            }

            return $compensation;
        });
    }

    private function createGeneratedTax(TaxAssessmentLetter $letter): Tax
    {
        $sourceTax = $letter->sourceTax;
        $billingSequence = app(BillingService::class)->getNextBillingSequence(
            $sourceTax->tax_object_id,
            (int) $sourceTax->masa_pajak_bulan,
            (int) $sourceTax->masa_pajak_tahun,
        );

        return Tax::create([
            'jenis_pajak_id' => $sourceTax->jenis_pajak_id,
            'sub_jenis_pajak_id' => $sourceTax->sub_jenis_pajak_id,
            'tax_object_id' => $sourceTax->tax_object_id,
            'user_id' => $sourceTax->user_id,
            'amount' => (float) $letter->total_assessment,
            'omzet' => (float) ($letter->base_amount ?? 0),
            'sanksi' => 0,
            'tarif_persentase' => 0,
            'status' => TaxStatus::Pending,
            'billing_code' => Tax::generateBillingCode(
                $sourceTax->jenisPajak?->kode ?? '41102',
                $letter->letter_type->generatedBillingTrailingSuffix(),
            ),
            'skpd_number' => $letter->document_number,
            'notes' => $this->buildGeneratedTaxNotes($letter),
            'payment_expired_at' => $letter->due_date,
            'masa_pajak_bulan' => $sourceTax->masa_pajak_bulan,
            'masa_pajak_tahun' => $sourceTax->masa_pajak_tahun,
            'pembetulan_ke' => 0,
            'billing_sequence' => $billingSequence,
            'parent_tax_id' => $sourceTax->id,
            'dasar_hukum' => $this->buildDasarHukum($letter),
        ]);
    }

    private function buildGeneratedTaxNotes(TaxAssessmentLetter $letter): string
    {
        return implode(' ', array_filter([
            'Terbit dari ' . $letter->letter_type->getLabel() . '.',
            'Nomor dokumen: ' . $letter->document_number . '.',
            'Billing sumber: ' . $letter->sourceTax?->billing_code . '.',
            $letter->notes,
        ]));
    }

    private function buildDasarHukum(TaxAssessmentLetter $letter): string
    {
        return match ($letter->letter_type) {
            TaxAssessmentLetterType::SKPDKB => 'Pasal 128 jo Pasal 130 ayat (1) atau ayat (2)',
            TaxAssessmentLetterType::SKPDKBT => 'Pasal 128 ayat (3) jo Pasal 130 ayat (3)',
            TaxAssessmentLetterType::SKPDLB => 'Pasal 129',
            TaxAssessmentLetterType::SKPDN => 'Pasal 128 ayat (4)',
        };
    }

    private function resolveInterestMonths(Tax $sourceTax, Carbon $issueDate, TaxAssessmentLetterType $letterType): int
    {
        if (!$letterType->allowsGeneratedBilling()) {
            return 0;
        }

        $dueDate = $sourceTax->payment_expired_at ? Carbon::parse($sourceTax->payment_expired_at) : $issueDate;

        return Tax::hitungBulanTerlambat($dueDate, $issueDate);
    }

    private function resolveInterestRate(TaxAssessmentLetterType $letterType, TaxAssessmentReason $reason): float
    {
        if ($letterType !== TaxAssessmentLetterType::SKPDKB) {
            return 0;
        }

        return match ($reason) {
            TaxAssessmentReason::Pemeriksaan => 0.018,
            TaxAssessmentReason::JabatanTidakSampaikanSptpd,
            TaxAssessmentReason::JabatanTidakKooperatif => 0.022,
            default => 0,
        };
    }

    private function resolveSurchargeRate(Tax $sourceTax, TaxAssessmentLetterType $letterType, TaxAssessmentReason $reason): float
    {
        if ($letterType === TaxAssessmentLetterType::SKPDKBT) {
            return 1.0;
        }

        if ($letterType !== TaxAssessmentLetterType::SKPDKB) {
            return 0;
        }

        if (!in_array($reason, [
            TaxAssessmentReason::JabatanTidakSampaikanSptpd,
            TaxAssessmentReason::JabatanTidakKooperatif,
        ], true)) {
            return 0;
        }

        return $sourceTax->isPbjt() ? 0.5 : 0.25;
    }

    private function assertReasonMatchesLetterType(TaxAssessmentLetterType $letterType, TaxAssessmentReason $reason): void
    {
        $allowed = match ($letterType) {
            TaxAssessmentLetterType::SKPDKB => [
                TaxAssessmentReason::Pemeriksaan,
                TaxAssessmentReason::JabatanTidakSampaikanSptpd,
                TaxAssessmentReason::JabatanTidakKooperatif,
            ],
            TaxAssessmentLetterType::SKPDKBT => [TaxAssessmentReason::DataBaru],
            TaxAssessmentLetterType::SKPDLB => [TaxAssessmentReason::LebihBayar],
            TaxAssessmentLetterType::SKPDN => [TaxAssessmentReason::Nihil],
        };

        if (!in_array($reason, $allowed, true)) {
            throw new InvalidArgumentException('Dasar penerbitan tidak sesuai dengan jenis surat ketetapan.');
        }
    }

    private function generateDocumentNumber(TaxAssessmentLetterType $letterType): string
    {
        $prefix = strtoupper($letterType->value);
        $year = now()->format('Y');
        $month = now()->format('m');

        $count = TaxAssessmentLetter::withTrashed()
            ->where('letter_type', $letterType->value)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        $number = str_pad((string) $count, 6, '0', STR_PAD_LEFT);

        return $prefix . '/' . $year . '/' . $month . '/' . $number;
    }
}