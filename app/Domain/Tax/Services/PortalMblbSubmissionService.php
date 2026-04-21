<?php

namespace App\Domain\Tax\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Instansi;
use App\Domain\Shared\Services\PortalAttachmentService;
use App\Domain\Tax\Models\PortalMblbSubmission;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PortalMblbSubmissionService
{
    public function __construct(
        private readonly BillingService $billingService,
        private readonly MblbService $mblbService,
        private readonly PortalAttachmentService $portalAttachmentService,
    ) {
    }

    public function createSubmission(
        User $user,
        TaxObject $taxObject,
        int $bulan,
        int $tahun,
        array $volumes,
        UploadedFile $attachment,
        ?string $notes = null,
        ?Instansi $instansi = null,
    ): PortalMblbSubmission {
        $details = $this->resolveDetailItems($volumes);
        $tarifPersen = (float) ($taxObject->tarif_persen ?: ($taxObject->jenisPajak?->tarif_default ?? 20));
        $opsenPersen = (float) ($taxObject->jenisPajak?->opsen_persen ?? 25);
        $calculation = $this->mblbService->calculateTax($details, $tarifPersen, $opsenPersen);

        if (empty($calculation['details'])) {
            throw ValidationException::withMessages([
                'volumes' => 'Masukkan volume minimal satu jenis mineral.',
            ]);
        }

        if (! $taxObject->isMultiBilling() && $this->hasPendingSubmissionForPeriod($taxObject, $bulan, $tahun)) {
            throw ValidationException::withMessages([
                'tax_object_id' => 'Pengajuan billing MBLB untuk masa pajak ini masih menunggu verifikasi.',
            ]);
        }

        $attachmentPath = $this->portalAttachmentService->storeMblbSupportingDocument($attachment);

        return PortalMblbSubmission::create([
            'jenis_pajak_id' => $taxObject->jenis_pajak_id,
            'sub_jenis_pajak_id' => $taxObject->sub_jenis_pajak_id,
            'tax_object_id' => $taxObject->id,
            'user_id' => $user->id,
            ...($instansi?->toTransactionAttributes() ?? []),
            'masa_pajak_bulan' => $bulan,
            'masa_pajak_tahun' => $tahun,
            'tarif_persen' => $tarifPersen,
            'opsen_persen' => $opsenPersen,
            'total_dpp' => $calculation['total_dpp'],
            'pokok_pajak' => $calculation['pokok_pajak'],
            'opsen' => $calculation['opsen'],
            'detail_items' => $calculation['details'],
            'attachment_path' => $attachmentPath,
            'notes' => $notes,
            'status' => 'pending',
        ]);
    }

    public function approveSubmission(PortalMblbSubmission $submission, User $reviewer, ?string $reviewNotes = null): Tax
    {
        return DB::transaction(function () use ($submission, $reviewer, $reviewNotes) {
            $submission->loadMissing(['taxObject.jenisPajak', 'taxObject.subJenisPajak', 'user']);

            if (! $submission->isPending()) {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan ini sudah diproses.',
                ]);
            }

            $taxObject = $submission->taxObject;

            if (! $taxObject) {
                throw ValidationException::withMessages([
                    'tax_object_id' => 'Objek pajak tidak ditemukan.',
                ]);
            }

            if (! $taxObject->isMultiBilling()) {
                $existingTax = $this->billingService->findExistingBillingForPeriod(
                    $taxObject->id,
                    $submission->masa_pajak_bulan,
                    $submission->masa_pajak_tahun,
                );

                if ($existingTax) {
                    throw ValidationException::withMessages([
                        'tax_object_id' => 'Billing aktif untuk masa pajak ini sudah terbit. Pengajuan perlu ditolak atau diperiksa ulang.',
                    ]);
                }
            }

            $billingSequence = $taxObject->isMultiBilling()
                ? $this->billingService->getNextBillingSequence(
                    $taxObject->id,
                    $submission->masa_pajak_bulan,
                    $submission->masa_pajak_tahun,
                )
                : 0;

            $tax = $this->mblbService->generateBilling([
                'jenis_pajak_id' => $submission->jenis_pajak_id,
                'sub_jenis_pajak_id' => $submission->sub_jenis_pajak_id,
                'tax_object_id' => $submission->tax_object_id,
                'user_id' => $submission->user_id,
                'instansi_id' => $submission->instansi_id,
                'instansi_nama' => $submission->instansi_nama,
                'instansi_kategori' => $submission->instansi_kategori?->value,
                'total_dpp' => (float) $submission->total_dpp,
                'pokok_pajak' => (float) $submission->pokok_pajak,
                'opsen' => (float) $submission->opsen,
                'tarif_persen' => (float) $submission->tarif_persen,
                'bulan' => $submission->masa_pajak_bulan,
                'tahun' => $submission->masa_pajak_tahun,
                'billing_sequence' => $billingSequence,
                'notes' => $submission->notes,
                'attachment_url' => $submission->attachment_path,
                'details' => $submission->detail_items ?? [],
            ]);

            $submission->update([
                'status' => 'approved',
                'processed_by' => $reviewer->id,
                'processed_at' => now(),
                'review_notes' => $reviewNotes,
                'approved_tax_id' => $tax->id,
                'rejection_reason' => null,
            ]);

            return $tax;
        });
    }

    public function rejectSubmission(PortalMblbSubmission $submission, User $reviewer, string $reason): void
    {
        if (! $submission->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan ini sudah diproses.',
            ]);
        }

        $submission->update([
            'status' => 'rejected',
            'processed_by' => $reviewer->id,
            'processed_at' => now(),
            'rejection_reason' => $reason,
            'review_notes' => null,
        ]);
    }

    public function hasPendingSubmissionForPeriod(TaxObject $taxObject, int $bulan, int $tahun): bool
    {
        return $this->pendingSubmissionQuery($taxObject, $bulan, $tahun)->exists();
    }

    private function pendingSubmissionQuery(TaxObject $taxObject, int $bulan, int $tahun)
    {
        $query = PortalMblbSubmission::query()
            ->where('status', 'pending')
            ->where('masa_pajak_bulan', $bulan)
            ->where('masa_pajak_tahun', $tahun);

        if ($taxObject->nopd) {
            $query->whereHas('taxObject', function ($builder) use ($taxObject) {
                $builder->where('nopd', $taxObject->nopd)
                    ->where('jenis_pajak_id', $taxObject->jenis_pajak_id);
            });

            return $query;
        }

        return $query->where('tax_object_id', $taxObject->id);
    }

    private function resolveDetailItems(array $volumes): array
    {
        return $this->mblbService->getAllMineralItems()
            ->map(function ($mineral) use ($volumes) {
                $volume = $volumes[$mineral->id] ?? null;

                return [
                    'harga_patokan_mblb_id' => $mineral->id,
                    'jenis_mblb' => $mineral->nama_mineral,
                    'volume' => (float) ($volume ?: 0),
                    'harga_patokan' => (float) $mineral->harga_patokan,
                ];
            })
            ->all();
    }
}