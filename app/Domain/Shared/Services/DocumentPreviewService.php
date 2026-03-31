<?php

namespace App\Domain\Shared\Services;

use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\AirTanah\Models\WaterObject;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Reklame\Models\ReklameObject;
use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxAssessmentCompensation;
use App\Domain\Tax\Models\TaxAssessmentLetter;
use App\Domain\Tax\Models\TaxMblbDetail;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Tax\Models\TaxSarangWaletDetail;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\TaxAssessmentLetterStatus;
use App\Enums\TaxAssessmentLetterType;
use App\Enums\TaxAssessmentReason;
use App\Enums\TaxStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class DocumentPreviewService
{
    private const PREVIEWS = [
        [
            'slug' => 'billing-regular',
            'category' => 'Billing',
            'label' => 'Billing SA Reguler',
            'description' => 'Billing self-assessment standar untuk PBJT restoran.',
            'builder' => 'buildBillingRegularPreview',
        ],
        [
            'slug' => 'billing-mblb',
            'category' => 'Billing',
            'label' => 'Billing SA MBLB',
            'description' => 'Billing dengan tabel detail mineral dan opsen MBLB.',
            'builder' => 'buildBillingMblbPreview',
        ],
        [
            'slug' => 'billing-sarang-walet',
            'category' => 'Billing',
            'label' => 'Billing SA Sarang Walet',
            'description' => 'Billing tahunan dengan detail sarang walet.',
            'builder' => 'buildBillingSarangWaletPreview',
        ],
        [
            'slug' => 'billing-pembetulan',
            'category' => 'Billing',
            'label' => 'Billing SA Pembetulan',
            'description' => 'Billing pembetulan dengan kredit pajak dari billing induk.',
            'builder' => 'buildBillingPembetulanPreview',
        ],
        [
            'slug' => 'sptpd-regular',
            'category' => 'SPTPD',
            'label' => 'SPTPD Reguler',
            'description' => 'SPTPD standar setelah billing lunas.',
            'builder' => 'buildSptpdRegularPreview',
        ],
        [
            'slug' => 'sptpd-mblb',
            'category' => 'SPTPD',
            'label' => 'SPTPD MBLB',
            'description' => 'SPTPD dengan detail mineral MBLB dan opsen.',
            'builder' => 'buildSptpdMblbPreview',
        ],
        [
            'slug' => 'sptpd-sarang-walet',
            'category' => 'SPTPD',
            'label' => 'SPTPD Sarang Walet',
            'description' => 'SPTPD tahunan untuk pajak sarang walet.',
            'builder' => 'buildSptpdSarangWaletPreview',
        ],
        [
            'slug' => 'stpd-auto',
            'category' => 'STPD',
            'label' => 'STPD Otomatis',
            'description' => 'STPD otomatis untuk billing yang sudah lunas dengan sanksi.',
            'builder' => 'buildStpdAutoPreview',
        ],
        [
            'slug' => 'stpd-manual-pokok',
            'category' => 'STPD',
            'label' => 'STPD Manual Pokok + Sanksi',
            'description' => 'STPD manual tipe pokok_sanksi dengan proyeksi tanggal bayar.',
            'builder' => 'buildStpdManualPokokPreview',
        ],
        [
            'slug' => 'stpd-manual-sanksi',
            'category' => 'STPD',
            'label' => 'STPD Manual Sanksi Saja',
            'description' => 'STPD manual tipe sanksi_saja dengan kode pembayaran alias.',
            'builder' => 'buildStpdManualSanksiPreview',
        ],
        [
            'slug' => 'skpd-reklame',
            'category' => 'SKPD',
            'label' => 'SKPD Reklame',
            'description' => 'SKPD reklame standar untuk objek reklame biasa.',
            'builder' => 'buildSkpdReklamePreview',
        ],
        [
            'slug' => 'skpd-reklame-sewa',
            'category' => 'SKPD',
            'label' => 'SKPD Reklame Sewa Pemkab',
            'description' => 'SKPD reklame dari permohonan sewa aset reklame pemkab.',
            'builder' => 'buildSkpdReklameSewaPreview',
        ],
        [
            'slug' => 'skpd-air-meter',
            'category' => 'SKPD',
            'label' => 'SKPD Air Tanah Meter',
            'description' => 'SKPD air tanah untuk objek meter air.',
            'builder' => 'buildSkpdAirMeterPreview',
        ],
        [
            'slug' => 'skpd-air-non-meter',
            'category' => 'SKPD',
            'label' => 'SKPD Air Tanah Non Meter',
            'description' => 'SKPD air tanah untuk objek non-meter.',
            'builder' => 'buildSkpdAirNonMeterPreview',
        ],
        [
            'slug' => 'tax-assessment-skpdkb',
            'category' => 'Surat Ketetapan',
            'label' => 'Surat Ketetapan SKPDKB',
            'description' => 'Ketetapan dengan billing turunan baru berakhiran 19.',
            'builder' => 'buildTaxAssessmentSkpdkbPreview',
        ],
        [
            'slug' => 'tax-assessment-skpdkbt',
            'category' => 'Surat Ketetapan',
            'label' => 'Surat Ketetapan SKPDKBT',
            'description' => 'Ketetapan kurang bayar tambahan dengan billing awal + SKPDKB sebagai telah dibayar.',
            'builder' => 'buildTaxAssessmentSkpdkbtPreview',
        ],
        [
            'slug' => 'tax-assessment-skpdlb',
            'category' => 'Surat Ketetapan',
            'label' => 'Surat Ketetapan SKPDLB',
            'description' => 'Ketetapan lebih bayar dengan tabel kompensasi kredit.',
            'builder' => 'buildTaxAssessmentSkpdlbPreview',
        ],
    ];

    public function catalog(): array
    {
        return array_map(function (array $preview): array {
            unset($preview['builder']);

            return $preview;
        }, self::PREVIEWS);
    }

    public function make(string $slug): array
    {
        foreach (self::PREVIEWS as $preview) {
            if ($preview['slug'] !== $slug) {
                continue;
            }

            return $this->{$preview['builder']}();
        }

        throw new InvalidArgumentException("Unknown document preview [{$slug}].");
    }

    private function buildBillingRegularPreview(): array
    {
        $fixture = $this->makeRegularTaxFixture();

        return $this->makePreviewDefinition(
            'documents.billing-sa',
            'preview-billing-regular.pdf',
            $this->buildTaxDocumentData($fixture['tax'], $fixture['taxObject'], $fixture['wajibPajak'])
        );
    }

    private function buildBillingMblbPreview(): array
    {
        $fixture = $this->makeMblbTaxFixture();

        return $this->makePreviewDefinition(
            'documents.billing-sa',
            'preview-billing-mblb.pdf',
            $this->buildTaxDocumentData(
                $fixture['tax'],
                $fixture['taxObject'],
                $fixture['wajibPajak'],
                [
                    'isMblb' => true,
                    'mblbDetails' => $fixture['mblbDetails'],
                ]
            )
        );
    }

    private function buildBillingSarangWaletPreview(): array
    {
        $fixture = $this->makeSarangWaletTaxFixture();

        return $this->makePreviewDefinition(
            'documents.billing-sa',
            'preview-billing-sarang-walet.pdf',
            $this->buildTaxDocumentData(
                $fixture['tax'],
                $fixture['taxObject'],
                $fixture['wajibPajak'],
                [
                    'isSarangWalet' => true,
                    'sarangWaletDetail' => $fixture['sarangWaletDetail'],
                ]
            )
        );
    }

    private function buildBillingPembetulanPreview(): array
    {
        $fixture = $this->makeRegularTaxFixture([
            'id' => 'preview-tax-billing-pembetulan',
            'billing_code' => '352210230300021102',
            'amount' => 1_550_000,
            'omzet' => 15_500_000,
            'sanksi' => 155_000,
            'payment_expired_at' => Carbon::create(2030, 4, 15),
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 1,
            'parent_tax_id' => 'preview-tax-parent',
        ]);

        $parentTax = $this->makeModel(Tax::class, [
            'id' => 'preview-tax-parent',
            'amount' => 1_200_000,
            'status' => TaxStatus::Paid,
            'billing_code' => '352210230300011102',
        ]);

        $fixture['tax']->setRelation('parent', $parentTax);

        return $this->makePreviewDefinition(
            'documents.billing-sa',
            'preview-billing-pembetulan.pdf',
            $this->buildTaxDocumentData(
                $fixture['tax'],
                $fixture['taxObject'],
                $fixture['wajibPajak'],
                [
                    'pembetulanKe' => 1,
                    'kreditPajak' => 1_200_000,
                    'parentPaid' => true,
                ]
            )
        );
    }

    private function buildSptpdRegularPreview(): array
    {
        $fixture = $this->makeRegularTaxFixture([
            'id' => 'preview-tax-sptpd-regular',
            'status' => TaxStatus::Paid,
            'billing_code' => '352210230300031102',
            'sptpd_number' => '352210230300031102',
            'paid_at' => Carbon::create(2030, 3, 25, 10, 15),
            'verified_at' => Carbon::create(2030, 3, 26, 9, 0),
            'payment_expired_at' => Carbon::create(2030, 4, 15),
        ]);

        return $this->makePreviewDefinition(
            'documents.sptpd',
            'preview-sptpd-regular.pdf',
            $this->buildTaxDocumentData($fixture['tax'], $fixture['taxObject'], $fixture['wajibPajak'])
        );
    }

    private function buildSptpdMblbPreview(): array
    {
        $fixture = $this->makeMblbTaxFixture([
            'id' => 'preview-tax-sptpd-mblb',
            'status' => TaxStatus::Paid,
            'billing_code' => '352210630300011906',
            'sptpd_number' => '352210630300011906',
            'paid_at' => Carbon::create(2030, 3, 26, 11, 0),
            'verified_at' => Carbon::create(2030, 3, 26, 14, 0),
            'payment_expired_at' => Carbon::create(2030, 4, 15),
        ]);

        return $this->makePreviewDefinition(
            'documents.sptpd',
            'preview-sptpd-mblb.pdf',
            $this->buildTaxDocumentData(
                $fixture['tax'],
                $fixture['taxObject'],
                $fixture['wajibPajak'],
                [
                    'isMblb' => true,
                    'mblbDetails' => $fixture['mblbDetails'],
                ]
            )
        );
    }

    private function buildSptpdSarangWaletPreview(): array
    {
        $fixture = $this->makeSarangWaletTaxFixture([
            'id' => 'preview-tax-sptpd-sarang-walet',
            'status' => TaxStatus::Paid,
            'billing_code' => '352210930300011109',
            'sptpd_number' => '352210930300011109',
            'paid_at' => Carbon::create(2030, 3, 27, 8, 45),
            'verified_at' => Carbon::create(2030, 3, 27, 9, 15),
            'payment_expired_at' => Carbon::create(2030, 4, 30),
        ]);

        return $this->makePreviewDefinition(
            'documents.sptpd',
            'preview-sptpd-sarang-walet.pdf',
            $this->buildTaxDocumentData(
                $fixture['tax'],
                $fixture['taxObject'],
                $fixture['wajibPajak'],
                [
                    'isSarangWalet' => true,
                    'sarangWaletDetail' => $fixture['sarangWaletDetail'],
                ]
            )
        );
    }

    private function buildStpdAutoPreview(): array
    {
        $fixture = $this->makeRegularTaxFixture([
            'id' => 'preview-tax-stpd-auto',
            'status' => TaxStatus::Paid,
            'billing_code' => '352210230300041102',
            'stpd_number' => '352210230300041102',
            'sanksi' => 187_500,
            'paid_at' => Carbon::create(2030, 4, 1, 10, 0),
            'payment_expired_at' => Carbon::create(2030, 4, 20),
        ]);

        return $this->makePreviewDefinition(
            'documents.stpd',
            'preview-stpd-auto.pdf',
            [
                ...$this->buildTaxDocumentData($fixture['tax'], $fixture['taxObject'], $fixture['wajibPajak']),
                'stpdDocumentNumber' => $fixture['tax']->stpd_number,
                'stpdPaymentCode' => $fixture['tax']->billing_code,
                'sanksi' => 187_500,
                'pimpinan' => $this->makePimpinan(),
                'sanksiBelumDibayar' => 187_500,
                'isSanksiBelumLunas' => false,
                'stpdManual' => null,
            ]
        );
    }

    private function buildStpdManualPokokPreview(): array
    {
        $fixture = $this->makeRegularTaxFixture([
            'id' => 'preview-tax-stpd-manual-pokok',
            'status' => TaxStatus::Pending,
            'billing_code' => '352210230300051102',
            'amount' => 1_350_000,
            'sanksi' => 94_500,
            'payment_expired_at' => Carbon::create(2030, 4, 22),
        ]);

        $stpdManual = $this->makeModel(StpdManual::class, [
            'id' => 'preview-stpd-manual-pokok',
            'tipe' => 'pokok_sanksi',
            'nomor_stpd' => 'STPD/2030/04/000123',
            'status' => 'disetujui',
            'bulan_terlambat' => 3,
            'sanksi_dihitung' => 94_500,
            'pokok_belum_dibayar' => 1_350_000,
            'proyeksi_tanggal_bayar' => Carbon::create(2030, 4, 22),
            'tanggal_buat' => Carbon::create(2030, 4, 10, 9, 0),
            'tanggal_verifikasi' => Carbon::create(2030, 4, 11, 13, 30),
        ]);

        return $this->makePreviewDefinition(
            'documents.stpd',
            'preview-stpd-manual-pokok.pdf',
            [
                ...$this->buildTaxDocumentData($fixture['tax'], $fixture['taxObject'], $fixture['wajibPajak']),
                'stpdDocumentNumber' => $stpdManual->nomor_stpd,
                'stpdPaymentCode' => $fixture['tax']->billing_code,
                'sanksi' => 94_500,
                'pimpinan' => $this->makePimpinan(),
                'sanksiBelumDibayar' => 94_500,
                'isSanksiBelumLunas' => false,
                'stpdManual' => $stpdManual,
            ]
        );
    }

    private function buildStpdManualSanksiPreview(): array
    {
        $fixture = $this->makeRegularTaxFixture([
            'id' => 'preview-tax-stpd-manual-sanksi',
            'status' => TaxStatus::Paid,
            'billing_code' => '352210230300061102',
            'stpd_payment_code' => '352210277300061102',
            'sanksi' => 78_000,
            'paid_at' => Carbon::create(2030, 4, 1, 9, 15),
            'payment_expired_at' => Carbon::create(2030, 4, 25),
        ]);

        $stpdManual = $this->makeModel(StpdManual::class, [
            'id' => 'preview-stpd-manual-sanksi',
            'tipe' => 'sanksi_saja',
            'nomor_stpd' => 'STPD/2030/04/000124',
            'status' => 'disetujui',
            'bulan_terlambat' => 2,
            'sanksi_dihitung' => 78_000,
            'pokok_belum_dibayar' => 0,
            'proyeksi_tanggal_bayar' => Carbon::create(2030, 4, 25),
            'tanggal_buat' => Carbon::create(2030, 4, 12, 10, 0),
            'tanggal_verifikasi' => Carbon::create(2030, 4, 12, 14, 0),
        ]);

        return $this->makePreviewDefinition(
            'documents.stpd',
            'preview-stpd-manual-sanksi.pdf',
            [
                ...$this->buildTaxDocumentData($fixture['tax'], $fixture['taxObject'], $fixture['wajibPajak']),
                'stpdDocumentNumber' => $stpdManual->nomor_stpd,
                'stpdPaymentCode' => $fixture['tax']->getPreferredPaymentCode(),
                'sanksi' => 78_000,
                'pimpinan' => $this->makePimpinan(),
                'sanksiBelumDibayar' => 78_000,
                'isSanksiBelumLunas' => true,
                'stpdManual' => $stpdManual,
            ]
        );
    }

    private function buildSkpdReklamePreview(): array
    {
        $fixture = $this->makeSkpdReklameFixture();

        return $this->makePreviewDefinition(
            'documents.skpd-reklame',
            'preview-skpd-reklame.pdf',
            [
                'skpd' => $fixture['skpd'],
                'pimpinan' => $fixture['pimpinan'],
                'isPdf' => true,
            ]
        );
    }

    private function buildSkpdReklameSewaPreview(): array
    {
        $fixture = $this->makeSkpdReklameFixture(isSewaPemkab: true);

        return $this->makePreviewDefinition(
            'documents.skpd-reklame',
            'preview-skpd-reklame-sewa.pdf',
            [
                'skpd' => $fixture['skpd'],
                'pimpinan' => $fixture['pimpinan'],
                'isPdf' => true,
            ]
        );
    }

    private function buildSkpdAirMeterPreview(): array
    {
        $fixture = $this->makeSkpdAirTanahFixture();

        return $this->makePreviewDefinition(
            'documents.skpd-air-tanah',
            'preview-skpd-air-meter.pdf',
            [
                'skpd' => $fixture['skpd'],
                'pimpinan' => $fixture['pimpinan'],
                'isPdf' => true,
            ]
        );
    }

    private function buildSkpdAirNonMeterPreview(): array
    {
        $fixture = $this->makeSkpdAirTanahFixture(usesMeter: false);

        return $this->makePreviewDefinition(
            'documents.skpd-air-tanah',
            'preview-skpd-air-non-meter.pdf',
            [
                'skpd' => $fixture['skpd'],
                'pimpinan' => $fixture['pimpinan'],
                'isPdf' => true,
            ]
        );
    }

    private function buildTaxAssessmentSkpdkbPreview(): array
    {
        $fixture = $this->makeRegularTaxFixture([
            'id' => 'preview-tax-source-ketetapan',
            'billing_code' => '352210230300071102',
            'status' => TaxStatus::Verified,
            'amount' => 2_100_000,
            'omzet' => 21_000_000,
        ]);

        $generatedTax = $this->makeModel(Tax::class, [
            'id' => 'preview-tax-generated-ketetapan',
            'billing_code' => '352210230300071919',
            'status' => TaxStatus::Pending,
            'payment_expired_at' => Carbon::create(2030, 5, 15),
        ]);

        $letter = $this->makeModel(TaxAssessmentLetter::class, [
            'id' => 'preview-letter-skpdkb',
            'letter_type' => TaxAssessmentLetterType::SKPDKB,
            'issuance_reason' => TaxAssessmentReason::Pemeriksaan,
            'status' => TaxAssessmentLetterStatus::Disetujui,
            'document_number' => 'SKPDKB/2030/04/000001',
            'issue_date' => Carbon::create(2030, 4, 14),
            'due_date' => Carbon::create(2030, 5, 15),
            'base_amount' => 2_100_000,
            'interest_rate' => 2.00,
            'interest_months' => 2,
            'interest_amount' => 84_000,
            'surcharge_rate' => 25.00,
            'surcharge_amount' => 525_000,
            'total_assessment' => 2_709_000,
            'available_credit' => 0,
            'notes' => 'Preview lokal untuk memeriksa layout surat ketetapan SKPDKB.',
            'verified_at' => Carbon::create(2030, 4, 14, 11, 30),
            'verified_by_name' => 'Verifikator Preview',
        ]);

        $letter->setRelation('generatedTax', $generatedTax);

        return $this->makePreviewDefinition(
            'documents.tax-assessment-letter',
            'preview-tax-assessment-skpdkb.pdf',
            [
                'letter' => $letter,
                'tax' => $fixture['tax'],
                'taxObject' => $fixture['taxObject'],
                'wajibPajak' => $fixture['wajibPajak'],
                'generatedTax' => $generatedTax,
                'pimpinan' => $this->makePimpinan(),
            ]
        );
    }

    private function buildTaxAssessmentSkpdkbtPreview(): array
    {
        $fixture = $this->makeRegularTaxFixture([
            'id' => 'preview-tax-source-ketetapan-bt',
            'billing_code' => '352210230300091102',
            'status' => TaxStatus::Verified,
            'amount' => 2_100_000,
            'omzet' => 21_000_000,
        ]);

        // Parent SKPDKB: billing turunan yang sudah dibayar
        $parentGeneratedTax = $this->makeModel(Tax::class, [
            'id' => 'preview-tax-parent-generated-bt',
            'billing_code' => '352210230300091919',
            'status' => TaxStatus::Paid,
            'amount' => 2_709_000,
        ]);

        $parentLetter = $this->makeModel(TaxAssessmentLetter::class, [
            'id' => 'preview-letter-skpdkb-parent',
            'letter_type' => TaxAssessmentLetterType::SKPDKB,
            'status' => TaxAssessmentLetterStatus::Disetujui,
            'document_number' => 'SKPDKB/2030/04/000002',
            'total_assessment' => 2_709_000,
        ]);
        $parentLetter->setRelation('generatedTax', $parentGeneratedTax);

        $generatedTax = $this->makeModel(Tax::class, [
            'id' => 'preview-tax-generated-ketetapan-bt',
            'billing_code' => '352210230300091919',
            'status' => TaxStatus::Pending,
            'payment_expired_at' => Carbon::create(2030, 6, 15),
        ]);

        $letter = $this->makeModel(TaxAssessmentLetter::class, [
            'id' => 'preview-letter-skpdkbt',
            'letter_type' => TaxAssessmentLetterType::SKPDKBT,
            'issuance_reason' => TaxAssessmentReason::DataBaru,
            'status' => TaxAssessmentLetterStatus::Disetujui,
            'document_number' => 'SKPDKBT/2030/05/000001',
            'issue_date' => Carbon::create(2030, 5, 20),
            'due_date' => Carbon::create(2030, 6, 15),
            'base_amount' => 3_500_000,
            'interest_rate' => 2.00,
            'interest_months' => 1,
            'interest_amount' => 70_000,
            'surcharge_rate' => 0,
            'surcharge_amount' => 0,
            'total_assessment' => 3_570_000,
            'available_credit' => 0,
            'notes' => 'Preview lokal untuk memeriksa layout surat ketetapan SKPDKBT.',
            'verified_at' => Carbon::create(2030, 5, 20, 10, 0),
            'verified_by_name' => 'Verifikator Preview',
        ]);

        $letter->setRelation('parentLetter', $parentLetter);
        $letter->setRelation('generatedTax', $generatedTax);

        return $this->makePreviewDefinition(
            'documents.tax-assessment-letter',
            'preview-tax-assessment-skpdkbt.pdf',
            [
                'letter' => $letter,
                'tax' => $fixture['tax'],
                'taxObject' => $fixture['taxObject'],
                'wajibPajak' => $fixture['wajibPajak'],
                'generatedTax' => $generatedTax,
                'pimpinan' => $this->makePimpinan(),
            ]
        );
    }

    private function buildTaxAssessmentSkpdlbPreview(): array
    {
        $fixture = $this->makeRegularTaxFixture([
            'id' => 'preview-tax-source-ketetapan-lb',
            'billing_code' => '352210230300081102',
            'status' => TaxStatus::Paid,
            'amount' => 1_800_000,
            'omzet' => 18_000_000,
        ]);

        $targetTax = $this->makeModel(Tax::class, [
            'id' => 'preview-tax-target-ketetapan-lb',
            'billing_code' => '352210230300091102',
            'status' => TaxStatus::Pending,
        ]);

        $compensation = $this->makeModel(TaxAssessmentCompensation::class, [
            'id' => 'preview-compensation-1',
            'allocation_amount' => 450_000,
        ]);
        $compensation->setRelation('targetTax', $targetTax);

        $letter = $this->makeModel(TaxAssessmentLetter::class, [
            'id' => 'preview-letter-skpdlb',
            'letter_type' => TaxAssessmentLetterType::SKPDLB,
            'issuance_reason' => TaxAssessmentReason::LebihBayar,
            'status' => TaxAssessmentLetterStatus::Disetujui,
            'document_number' => 'SKPDLB/2030/04/000001',
            'issue_date' => Carbon::create(2030, 4, 16),
            'due_date' => null,
            'base_amount' => 900_000,
            'interest_rate' => 0,
            'interest_months' => 0,
            'interest_amount' => 0,
            'surcharge_rate' => 0,
            'surcharge_amount' => 0,
            'total_assessment' => 900_000,
            'available_credit' => 450_000,
            'notes' => 'Preview lokal untuk memeriksa tabel kompensasi kredit pada SKPDLB.',
            'verified_at' => Carbon::create(2030, 4, 16, 10, 45),
            'verified_by_name' => 'Verifikator Preview',
        ]);

        $letter->setRelation('compensations', collect([$compensation]));

        return $this->makePreviewDefinition(
            'documents.tax-assessment-letter',
            'preview-tax-assessment-skpdlb.pdf',
            [
                'letter' => $letter,
                'tax' => $fixture['tax'],
                'taxObject' => $fixture['taxObject'],
                'wajibPajak' => $fixture['wajibPajak'],
                'generatedTax' => null,
                'pimpinan' => $this->makePimpinan(),
            ]
        );
    }

    private function makePreviewDefinition(string $view, string $filename, array $data): array
    {
        return [
            'view' => $view,
            'filename' => $filename,
            'data' => $data,
        ];
    }

    private function buildTaxDocumentData(Tax $tax, TaxObject $taxObject, WajibPajak $wajibPajak, array $overrides = []): array
    {
        return array_merge([
            'tax' => $tax,
            'taxObject' => $taxObject,
            'wajibPajak' => $wajibPajak,
            'isPdf' => true,
            'pembetulanKe' => (int) ($tax->pembetulan_ke ?? 0),
            'kreditPajak' => 0,
            'parentPaid' => false,
            'isMblb' => false,
            'mblbDetails' => collect(),
            'isSarangWalet' => false,
            'sarangWaletDetail' => null,
        ], $overrides);
    }

    private function makeRegularTaxFixture(array $taxOverrides = []): array
    {
        $portalUser = $this->makeUser([
            'id' => 'preview-user-portal-regular',
            'name' => 'Preview Restoran',
            'nama_lengkap' => 'Preview Restoran',
            'email' => 'preview-restoran@example.test',
            'role' => 'user',
            'nik' => '3522011234567890',
            'alamat' => 'Jl. Panglima Sudirman No. 12',
        ]);
        $jenisPajak = $this->makeJenisPajak('preview-jp-restoran', '41102', 'Makanan dan/atau Minuman');
        $subJenisPajak = $this->makeSubJenisPajak('preview-sjp-restoran', 'RESTORAN_REGULER', 'Restoran Reguler');
        $wajibPajak = $this->makeWajibPajak($portalUser, [
            'id' => 'preview-wp-restoran',
            'npwpd' => 'P10000000999',
            'nama_lengkap' => 'PT Preview Kuliner',
            'alamat' => 'Jl. Veteran No. 88, Bojonegoro',
            'tipe_wajib_pajak' => 'perusahaan',
            'nama_perusahaan' => 'PT Preview Kuliner',
        ]);
        $taxObject = $this->makeModel(TaxObject::class, [
            'id' => 'preview-tax-object-restoran',
            'nama_objek_pajak' => 'Kedai Bumi Angling',
            'nama_usaha' => 'Kedai Bumi Angling',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => $wajibPajak->npwpd,
            'nopd' => 2001,
            'alamat_objek' => 'Jl. Untung Suropati No. 21',
            'kelurahan' => 'Kadipaten',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => 10,
            'is_opd' => false,
            'is_insidentil' => false,
        ]);
        $tax = $this->makeModel(Tax::class, array_merge([
            'id' => 'preview-tax-billing-regular',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'tax_object_id' => $taxObject->id,
            'user_id' => $portalUser->id,
            'amount' => 1_200_000,
            'omzet' => 12_000_000,
            'sanksi' => 120_000,
            'opsen' => 0,
            'tarif_persentase' => 10,
            'status' => TaxStatus::Pending,
            'billing_code' => '352210230300011102',
            'payment_expired_at' => Carbon::create(2030, 4, 15),
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2030,
            'pembetulan_ke' => 0,
            'billing_sequence' => 11,
        ], $taxOverrides));

        $tax->setRelation('jenisPajak', $jenisPajak);
        $tax->setRelation('subJenisPajak', $subJenisPajak);
        $tax->setRelation('taxObject', $taxObject);
        $tax->setRelation('user', $portalUser);
        $taxObject->setRelation('jenisPajak', $jenisPajak);
        $taxObject->setRelation('subJenisPajak', $subJenisPajak);

        return compact('tax', 'taxObject', 'wajibPajak');
    }

    private function makeMblbTaxFixture(array $taxOverrides = []): array
    {
        $portalUser = $this->makeUser([
            'id' => 'preview-user-portal-mblb',
            'name' => 'Preview Tambang',
            'nama_lengkap' => 'Preview Tambang',
            'email' => 'preview-mblb@example.test',
            'role' => 'user',
            'nik' => '3522010000111122',
            'alamat' => 'Jl. Sultan Hasanuddin No. 17',
        ]);
        $jenisPajak = $this->makeJenisPajak('preview-jp-mblb', '41106', 'MBLB');
        $subJenisPajak = $this->makeSubJenisPajak('preview-sjp-mblb', 'MBLB_WAPU', 'MBLB Pemungut');
        $wajibPajak = $this->makeWajibPajak($portalUser, [
            'id' => 'preview-wp-mblb',
            'npwpd' => 'P10000000888',
            'nama_lengkap' => 'CV Preview Mineral',
            'alamat' => 'Jl. Basuki Rahmat No. 45, Bojonegoro',
            'tipe_wajib_pajak' => 'perusahaan',
            'nama_perusahaan' => 'CV Preview Mineral',
        ]);
        $taxObject = $this->makeModel(TaxObject::class, [
            'id' => 'preview-tax-object-mblb',
            'nama_objek_pajak' => 'Area Tambang Galian C',
            'nama_usaha' => 'Area Tambang Galian C',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => $wajibPajak->npwpd,
            'nopd' => 3001,
            'alamat_objek' => 'Desa Dander, Bojonegoro',
            'kelurahan' => 'Dander',
            'kecamatan' => 'Dander',
            'tarif_persen' => 20,
            'is_opd' => false,
            'is_insidentil' => false,
        ]);
        $tax = $this->makeModel(Tax::class, array_merge([
            'id' => 'preview-tax-billing-mblb',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'tax_object_id' => $taxObject->id,
            'user_id' => $portalUser->id,
            'amount' => 900_000,
            'omzet' => 4_500_000,
            'sanksi' => 45_000,
            'opsen' => 225_000,
            'tarif_persentase' => 20,
            'status' => TaxStatus::Pending,
            'billing_code' => '352210630300011906',
            'payment_expired_at' => Carbon::create(2030, 4, 15),
            'masa_pajak_bulan' => 3,
            'masa_pajak_tahun' => 2030,
            'billing_sequence' => 19,
        ], $taxOverrides));

        $tax->setRelation('jenisPajak', $jenisPajak);
        $tax->setRelation('subJenisPajak', $subJenisPajak);
        $tax->setRelation('taxObject', $taxObject);
        $tax->setRelation('user', $portalUser);

        $mblbDetails = collect([
            $this->makeModel(TaxMblbDetail::class, [
                'id' => 'preview-mblb-detail-1',
                'jenis_mblb' => 'Batu Kapur',
                'volume' => 150.50,
                'harga_patokan' => 12_000,
                'subtotal_dpp' => 1_806_000,
            ]),
            $this->makeModel(TaxMblbDetail::class, [
                'id' => 'preview-mblb-detail-2',
                'jenis_mblb' => 'Sirtu',
                'volume' => 95.00,
                'harga_patokan' => 28_357.89,
                'subtotal_dpp' => 2_694_000,
            ]),
        ]);

        return compact('tax', 'taxObject', 'wajibPajak', 'mblbDetails');
    }

    private function makeSarangWaletTaxFixture(array $taxOverrides = []): array
    {
        $portalUser = $this->makeUser([
            'id' => 'preview-user-portal-walet',
            'name' => 'Preview Walet',
            'nama_lengkap' => 'Preview Walet',
            'email' => 'preview-walet@example.test',
            'role' => 'user',
            'nik' => '3522012222333344',
            'alamat' => 'Jl. Teuku Umar No. 9',
        ]);
        $jenisPajak = $this->makeJenisPajak('preview-jp-walet', '41109', 'Sarang Burung Walet');
        $subJenisPajak = $this->makeSubJenisPajak('preview-sjp-walet', 'WALET_RUMAH', 'Rumah Walet');
        $wajibPajak = $this->makeWajibPajak($portalUser, [
            'id' => 'preview-wp-walet',
            'npwpd' => 'P10000000777',
            'nama_lengkap' => 'CV Preview Walet',
            'alamat' => 'Jl. Rajawali No. 11, Bojonegoro',
            'tipe_wajib_pajak' => 'perusahaan',
            'nama_perusahaan' => 'CV Preview Walet',
        ]);
        $taxObject = $this->makeModel(TaxObject::class, [
            'id' => 'preview-tax-object-walet',
            'nama_objek_pajak' => 'Gedung Walet Angkasa',
            'nama_usaha' => 'Gedung Walet Angkasa',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => $wajibPajak->npwpd,
            'nopd' => 4001,
            'alamat_objek' => 'Jl. Veteran Timur No. 30',
            'kelurahan' => 'Sumbang',
            'kecamatan' => 'Bojonegoro',
            'tarif_persen' => 10,
            'is_opd' => false,
            'is_insidentil' => false,
        ]);
        $tax = $this->makeModel(Tax::class, array_merge([
            'id' => 'preview-tax-billing-walet',
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'tax_object_id' => $taxObject->id,
            'user_id' => $portalUser->id,
            'amount' => 240_000,
            'omzet' => 2_400_000,
            'sanksi' => 24_000,
            'opsen' => 0,
            'tarif_persentase' => 10,
            'status' => TaxStatus::Pending,
            'billing_code' => '352210930300011109',
            'payment_expired_at' => Carbon::create(2030, 12, 31),
            'masa_pajak_bulan' => null,
            'masa_pajak_tahun' => 2030,
            'billing_sequence' => 11,
        ], $taxOverrides));

        $tax->setRelation('jenisPajak', $jenisPajak);
        $tax->setRelation('subJenisPajak', $subJenisPajak);
        $tax->setRelation('taxObject', $taxObject);
        $tax->setRelation('user', $portalUser);

        $sarangWaletDetail = $this->makeModel(TaxSarangWaletDetail::class, [
            'id' => 'preview-sarang-walet-detail',
            'jenis_sarang' => 'Sarang Walet Putih',
            'volume_kg' => 12.00,
            'harga_patokan' => 200_000,
            'subtotal_dpp' => 2_400_000,
        ]);

        return compact('tax', 'taxObject', 'wajibPajak', 'sarangWaletDetail');
    }

    private function makeSkpdReklameFixture(bool $isSewaPemkab = false): array
    {
        $pimpinan = $this->makePimpinan();
        $jenisPajak = $this->makeJenisPajak('preview-jp-reklame', '41104', 'Reklame');
        $subJenisPajak = $this->makeSubJenisPajak('preview-sjp-reklame', 'REKLAME_PAPAN', 'Papan/Billboard');
        $reklameObject = $this->makeModel(ReklameObject::class, [
            'id' => 'preview-reklame-object',
            'nama_objek_pajak' => 'Billboard Simpang Lima',
            'alamat_objek' => 'Jl. Ahmad Yani Simpang Lima',
            'npwpd' => 'P10000000666',
            'nopd' => 5001,
            'kelurahan' => 'Kauman',
            'kecamatan' => 'Bojonegoro',
            'bentuk' => 'persegi',
            'panjang' => 6,
            'lebar' => 3,
            'jumlah_muka' => 2,
            'kelompok_lokasi' => 'A',
        ]);

        $skpd = $this->makeModel(SkpdReklame::class, [
            'id' => $isSewaPemkab ? 'preview-skpd-reklame-sewa' : 'preview-skpd-reklame',
            'nomor_skpd' => $isSewaPemkab ? 'SKPD-RKL/2030/04/000099' : 'SKPD-RKL/2030/04/000098',
            'tax_object_id' => $reklameObject->id,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'npwpd' => 'P10000000666',
            'nik_wajib_pajak' => '3522011111222233',
            'nama_wajib_pajak' => 'PT Preview Reklame',
            'alamat_wajib_pajak' => 'Jl. Panglima Polim No. 14',
            'nama_reklame' => 'Billboard Simpang Lima',
            'jenis_reklame' => 'Billboard',
            'alamat_reklame' => 'Jl. Ahmad Yani Simpang Lima',
            'kelompok_lokasi' => 'A',
            'bentuk' => 'persegi',
            'panjang' => 6,
            'lebar' => 3,
            'luas_m2' => 18,
            'jumlah_muka' => 2,
            'lokasi_penempatan' => 'luar_ruangan',
            'jenis_produk' => 'non_rokok',
            'jumlah_reklame' => 1,
            'satuan_waktu' => 'perBulan',
            'satuan_label' => 'Bln/m²',
            'durasi' => 3,
            'tarif_pokok' => 250_000,
            'nspr' => 250_000,
            'njopr' => 0,
            'penyesuaian_lokasi' => 1,
            'penyesuaian_produk' => 1,
            'nilai_strategis' => 150_000,
            'pokok_pajak_dasar' => 4_500_000,
            'masa_berlaku_mulai' => Carbon::create(2030, 4, 1),
            'masa_berlaku_sampai' => Carbon::create(2030, 6, 30),
            'jatuh_tempo' => Carbon::create(2030, 4, 20),
            'dasar_pengenaan' => 4_650_000,
            'jumlah_pajak' => 4_650_000,
            'status' => 'disetujui',
            'tanggal_buat' => Carbon::create(2030, 3, 28, 9, 0),
            'petugas_nama' => 'Petugas Preview',
            'pimpinan_id' => $pimpinan->id,
            'kode_billing' => '352210430300011104',
            'dasar_hukum' => 'Perda Pajak Reklame Preview',
            'aset_reklame_pemkab_id' => $isSewaPemkab ? 'preview-aset-reklame' : null,
            'permohonan_sewa_id' => $isSewaPemkab ? 'preview-permohonan-sewa' : null,
        ]);

        $skpd->setRelation('reklameObject', $reklameObject);
        $skpd->setRelation('jenisPajak', $jenisPajak);
        $skpd->setRelation('subJenisPajak', $subJenisPajak);

        if ($isSewaPemkab) {
            $aset = $this->makeModel(AsetReklamePemkab::class, [
                'id' => 'preview-aset-reklame',
                'kode_aset' => 'AR-001',
                'lokasi' => 'Jl. Ahmad Yani Simpang Lima',
            ]);
            $permohonan = $this->makeModel(PermohonanSewaReklame::class, [
                'id' => 'preview-permohonan-sewa',
                'jenis_reklame_dipasang' => 'Iklan Event Daerah',
            ]);

            $skpd->setRelation('asetReklamePemkab', $aset);
            $skpd->setRelation('permohonanSewa', $permohonan);
        }

        return compact('skpd', 'pimpinan');
    }

    private function makeSkpdAirTanahFixture(bool $usesMeter = true): array
    {
        $pimpinan = $this->makePimpinan();
        $jenisPajak = $this->makeJenisPajak('preview-jp-air', '41108', 'Air Tanah');
        $subJenisPajak = $this->makeSubJenisPajak('preview-sjp-air', 'ABT_METER', 'Air Tanah Umum');
        $waterObject = $this->makeModel(WaterObject::class, [
            'id' => $usesMeter ? 'preview-water-object-meter' : 'preview-water-object-non-meter',
            'nama_objek_pajak' => $usesMeter ? 'Sumur Produksi Timur' : 'Sumber Air Non Meter Barat',
            'alamat_objek' => 'Jl. Letda Sucipto No. 5',
            'npwpd' => 'P10000000555',
            'nopd' => $usesMeter ? 6001 : 6002,
            'kriteria_sda' => '2',
            'kelompok_pemakaian' => '3',
            'uses_meter' => $usesMeter,
        ]);
        $skpd = $this->makeModel(SkpdAirTanah::class, [
            'id' => $usesMeter ? 'preview-skpd-air-meter' : 'preview-skpd-air-non-meter',
            'nomor_skpd' => $usesMeter ? 'SKPD-ABT/2030/04/000031' : 'SKPD-ABT/2030/04/000032',
            'tax_object_id' => $waterObject->id,
            'jenis_pajak_id' => $jenisPajak->id,
            'sub_jenis_pajak_id' => $subJenisPajak->id,
            'nik_wajib_pajak' => '3522014444555566',
            'nama_wajib_pajak' => 'PT Preview Air',
            'alamat_wajib_pajak' => 'Jl. Diponegoro No. 50',
            'nama_objek' => $waterObject->nama_objek_pajak,
            'alamat_objek' => $waterObject->alamat_objek,
            'nopd' => (string) $waterObject->nopd,
            'kecamatan' => 'Bojonegoro',
            'kelurahan' => 'Mulyoagung',
            'meter_reading_before' => $usesMeter ? 120 : 0,
            'meter_reading_after' => $usesMeter ? 168 : 0,
            'usage' => $usesMeter ? 48 : 35,
            'periode_bulan' => '2030-04',
            'jatuh_tempo' => Carbon::create(2030, 5, 15),
            'tarif_per_m3' => json_encode([
                ['min_vol' => 0, 'max_vol' => 20, 'npa' => 1500],
                ['min_vol' => 21, 'max_vol' => 99999999, 'npa' => 2000],
            ]),
            'dasar_pengenaan' => 78_000,
            'tarif_persen' => 20,
            'jumlah_pajak' => 15_600,
            'status' => 'disetujui',
            'tanggal_buat' => Carbon::create(2030, 4, 5, 10, 30),
            'petugas_nama' => 'Petugas Preview',
            'pimpinan_id' => $pimpinan->id,
            'kode_billing' => $usesMeter ? '352210830300011108' : '352210830300021108',
            'dasar_hukum' => 'Perda Air Tanah Preview',
        ]);

        $skpd->setRelation('waterObject', $waterObject);
        $skpd->setRelation('jenisPajak', $jenisPajak);
        $skpd->setRelation('subJenisPajak', $subJenisPajak);

        return compact('skpd', 'pimpinan');
    }

    private function makeUser(array $attributes): User
    {
        return $this->makeModel(User::class, array_merge([
            'status' => 'verified',
            'navigation_mode' => 'topbar',
        ], $attributes));
    }

    private function makeWajibPajak(User $user, array $attributes = []): WajibPajak
    {
        return $this->makeModel(WajibPajak::class, array_merge([
            'user_id' => $user->id,
            'nik' => $user->nik,
            'nama_lengkap' => $user->nama_lengkap,
            'alamat' => $user->alamat,
            'status' => 'disetujui',
            'tanggal_daftar' => Carbon::create(2030, 1, 10, 8, 0),
            'tanggal_verifikasi' => Carbon::create(2030, 1, 12, 9, 0),
            'npwpd' => 'P10000000999',
            'nopd' => 1,
        ], $attributes));
    }

    private function makePimpinan(): Pimpinan
    {
        return $this->makeModel(Pimpinan::class, [
            'id' => 'preview-pimpinan',
            'kab' => 'Bojonegoro',
            'opd' => 'Badan Pendapatan Daerah',
            'jabatan' => 'Kepala Badan Pendapatan Daerah',
            'nama' => 'Drs. Preview Pimpinan',
            'nip' => '196512101990031005',
        ]);
    }

    private function makeJenisPajak(string $id, string $kode, string $nama): JenisPajak
    {
        return $this->makeModel(JenisPajak::class, [
            'id' => $id,
            'kode' => $kode,
            'nama' => $nama,
        ]);
    }

    private function makeSubJenisPajak(string $id, string $kode, string $nama): SubJenisPajak
    {
        return $this->makeModel(SubJenisPajak::class, [
            'id' => $id,
            'kode' => $kode,
            'nama' => $nama,
        ]);
    }

    private function makeModel(string $class, array $attributes): Model
    {
        $model = new $class();
        $model->forceFill($attributes);

        return $model;
    }
}