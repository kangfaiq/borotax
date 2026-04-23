<?php

namespace App\Filament\Pages;

use Exception;
use App\Domain\Master\Models\Instansi;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Shared\Services\DecimalInputNormalizer;
use App\Filament\Pages\Concerns\InteractsWithDuplicateBillingInfo;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TarifPajak;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Tax\Services\BillingService;
use App\Domain\Tax\Services\PpjService;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Auth\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;

class BuatBillingSelfAssessment extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithDuplicateBillingInfo;

    protected static string | \BackedEnum | null $navigationIcon  = 'heroicon-o-document-plus';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Petugas';
    protected static ?string $navigationLabel = 'Buat Billing Self';
    protected static ?string $title           = 'Buat Billing Self';
    protected static ?int    $navigationSort  = 3;
    protected string  $view            = 'filament.pages.buat-billing-self-assessment';

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'petugas']);
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'petugas']);
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    // ── State: Search panel (left) ──────────────────────────────────────────
    public ?string $searchKeyword    = null;
    public array   $searchResults   = [];
    public ?string $expandedDetailId = null;

    // ── State: Form panel (right) ───────────────────────────────────────────
    public ?string $selectedTaxObjectId   = null;
    public ?array  $selectedTaxObjectData = null;
    public ?array  $wajibPajakData        = null;
    public ?float  $omzet                 = null;
    public ?int    $masaPajakBulan        = null;
    public ?int    $masaPajakTahun        = null;
    public ?string $keterangan            = null;
    public ?string $instansiId            = null;
    public array   $instansiOptions       = [];

    // ── State: PPJ-specific fields ──────────────────────────────────────────
    public ?float  $ppjPokokPajak              = null;
    public string|float|null $ppjKapasitasKva = null;
    public string|float|null $ppjTingkatPenggunaanPersen = null;
    public string|float|null $ppjJangkaWaktuJam = null;
    public ?string $ppjHargaSatuanListrikId    = null;
    public ?float  $ppjHargaSatuan             = null;
    public array   $ppjHargaSatuanOptions      = [];

    // ── State: Duplicate confirmation modal ─────────────────────────────────
    public ?array  $existingBillingInfo    = null;
    public bool    $showDuplicateConfirm   = false;
    public string  $duplicateConfirmTitle  = '';
    public string  $duplicateConfirmMessage = '';
    public bool    $showSkippedMonthConfirm = false;
    public bool    $skipMonthWarningAcknowledged = false;
    public string  $skippedMonthConfirmTitle = '';
    public string  $skippedMonthConfirmMessage = '';

    // ── State: Success panel (right) ────────────────────────────────────────
    public ?array $billingResult = null;

    // ────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->masaPajakBulan = (int) date('n');
        $this->masaPajakTahun = (int) date('Y');
        $this->loadInstansiOptions();
    }

    // ── Live Search ─────────────────────────────────────────────────────────

    public function updatedSearchKeyword(): void
    {
        $this->search();
    }

    public function search(): void
    {
        $keyword = trim($this->searchKeyword ?? '');

        if (strlen($keyword) < 3) {
            $this->searchResults = [];
            return;
        }

        try {
            $results = [];

            // Kode pajak PBJT yang diizinkan untuk billing self-assessment
            $allowedKodePajak = ['41101', '41102', '41103', '41105', '41107'];
            $allowedJenisPajakIds = JenisPajak::whereIn('kode', $allowedKodePajak)->pluck('id');

            // NIK — all-digit ≥ 5 chars
            if (ctype_digit($keyword) && strlen($keyword) >= 5) {
                $nikHash = WajibPajak::generateHash($keyword);
                $results = TaxObject::where('nik_hash', $nikHash)
                    ->where('is_active', true)
                    ->whereIn('jenis_pajak_id', $allowedJenisPajakIds)
                    ->with(['subJenisPajak', 'jenisPajak'])
                    ->get()
                    ->map(fn ($obj) => $this->mapTaxObject($obj))
                    ->toArray();
            }

            // NPWPD / nama / NIK (decrypt + filter)
            if (empty($results)) {
                $kw = strtolower($keyword);
                $results = TaxObject::where('is_active', true)
                    ->whereIn('jenis_pajak_id', $allowedJenisPajakIds)
                    ->with(['subJenisPajak', 'jenisPajak'])
                    ->get()
                    ->filter(fn ($obj) =>
                        str_contains(strtolower($obj->npwpd ?? ''), $kw)
                        || str_contains(strtolower($obj->nama_objek_pajak ?? ''), $kw)
                        || str_contains(strtolower($obj->nik ?? ''), $kw)
                    )
                    ->take(20)
                    ->map(fn ($obj) => $this->mapTaxObject($obj))
                    ->values()
                    ->toArray();
            }

            $this->searchResults     = $results;
            $this->expandedDetailId  = null;
        } catch (Exception $e) {
            $this->searchResults = [];
        }
    }

    public function searchTag(string $tag): void
    {
        $this->searchKeyword = $tag;
        $this->search();
    }

    public function toggleDetail(string $id): void
    {
        $this->expandedDetailId = $this->expandedDetailId === $id ? null : $id;
    }

    // ── Object Selection ────────────────────────────────────────────────────

    /**
     * User clicks a result card → populate right panel form.
     */
    public function selectObject(string $id): void
    {
        $selected = collect($this->searchResults)->firstWhere('id', $id);
        if (!$selected) return;

        $this->selectedTaxObjectId   = $id;
        $this->selectedTaxObjectData = $selected;
        $this->masaPajakBulan        = $selected['next_bulan'];
        $this->masaPajakTahun        = $selected['next_tahun'];
        $this->omzet                 = null;
        $this->existingBillingInfo   = null;
        $this->billingResult         = null;
        $this->showDuplicateConfirm  = false;
        $this->showSkippedMonthConfirm = false;
        $this->skipMonthWarningAcknowledged = false;
        $this->keterangan            = null;
        $this->instansiId            = null;

        // Reset PPJ fields
        $this->ppjPokokPajak              = null;
        $this->ppjKapasitasKva            = null;
        $this->ppjTingkatPenggunaanPersen = null;
        $this->ppjJangkaWaktuJam          = null;
        $this->ppjHargaSatuanListrikId    = null;
        $this->ppjHargaSatuan             = null;

        // Load harga satuan listrik options for PPJ Non-PLN
        $subJenisKode = $selected['sub_jenis_kode'] ?? null;
        if ($subJenisKode === 'PPJ_DIHASILKAN_SENDIRI') {
            $this->ppjHargaSatuanOptions = app(PpjService::class)->getAllHargaSatuan()
                ->map(fn ($h) => [
                    'id' => $h->id,
                    'label' => $h->nama_wilayah . ' — Rp ' . number_format((float) $h->harga_per_kwh, 0, ',', '.') . '/kWh',
                    'harga' => (float) $h->harga_per_kwh,
                ])
                ->values()
                ->toArray();
        } else {
            $this->ppjHargaSatuanOptions = [];
        }

        // Load WP data from nik_hash on the tax object
        $this->wajibPajakData = null;
        if (!empty($selected['nik_hash'])) {
            $wp = WajibPajak::where('nik_hash', $selected['nik_hash'])
                ->where('status', 'disetujui')
                ->first();

            if ($wp) {
                $this->wajibPajakData = [
                    'id'           => $wp->id,
                    'user_id'      => $wp->user_id,
                    'nama_lengkap' => $wp->nama_lengkap,
                    'npwpd'        => $wp->npwpd,
                    'tipe'         => $wp->tipe_wajib_pajak,
                ];
            }
        }
    }

    // ── Billing Submission ──────────────────────────────────────────────────

    /**
     * "Terbitkan Billing" button: validate → check duplicate → modal or generate.
     */
    public function updatedPpjHargaSatuanListrikId(): void
    {
        $selected = collect($this->ppjHargaSatuanOptions)->firstWhere('id', $this->ppjHargaSatuanListrikId);
        $this->ppjHargaSatuan = $selected['harga'] ?? null;
    }

    /**
     * Check if selected object is PPJ type.
     */
    public function isPpjSelected(): bool
    {
        return ($this->selectedTaxObjectData['sub_jenis_kode'] ?? null) === 'PPJ_SUMBER_LAIN'
            || ($this->selectedTaxObjectData['sub_jenis_kode'] ?? null) === 'PPJ_DIHASILKAN_SENDIRI';
    }

    public function isPpjSumberLainSelected(): bool
    {
        return ($this->selectedTaxObjectData['sub_jenis_kode'] ?? null) === 'PPJ_SUMBER_LAIN';
    }

    public function isPpjNonPlnSelected(): bool
    {
        return ($this->selectedTaxObjectData['sub_jenis_kode'] ?? null) === 'PPJ_DIHASILKAN_SENDIRI';
    }

    private function normalizePpjDecimalInputs(): void
    {
        $this->ppjKapasitasKva = DecimalInputNormalizer::toFloat($this->ppjKapasitasKva);
        $this->ppjTingkatPenggunaanPersen = DecimalInputNormalizer::toFloat($this->ppjTingkatPenggunaanPersen);
        $this->ppjJangkaWaktuJam = DecimalInputNormalizer::toFloat($this->ppjJangkaWaktuJam);
    }

    public function terbitkanBilling(): void
    {
        $this->normalizePpjDecimalInputs();

        $isPpjSumberLain = $this->isPpjSumberLainSelected();
        $isPpjNonPln = $this->isPpjNonPlnSelected();

        // Validate PPJ Sumber Lain (PLN)
        if ($isPpjSumberLain) {
            if (!$this->ppjPokokPajak || $this->ppjPokokPajak <= 0) {
                Notification::make()->warning()->title('Masukkan pokok pajak terutang yang valid (lebih dari 0)')->send();
                return;
            }
        }
        // Validate PPJ Dihasilkan Sendiri (Non PLN)
        elseif ($isPpjNonPln) {
            if (!$this->ppjKapasitasKva || $this->ppjKapasitasKva <= 0) {
                Notification::make()->warning()->title('Masukkan kapasitas tersedia (kVA) yang valid')->send();
                return;
            }
            if (!$this->ppjTingkatPenggunaanPersen || $this->ppjTingkatPenggunaanPersen <= 0 || $this->ppjTingkatPenggunaanPersen > 100) {
                Notification::make()->warning()->title('Masukkan tingkat penggunaan listrik (1-100%)')->send();
                return;
            }
            if (!$this->ppjJangkaWaktuJam || $this->ppjJangkaWaktuJam <= 0) {
                Notification::make()->warning()->title('Masukkan jangka waktu pemakaian (jam) yang valid')->send();
                return;
            }
            if (!$this->ppjHargaSatuan || $this->ppjHargaSatuan <= 0) {
                Notification::make()->warning()->title('Pilih harga satuan listrik dari master data')->send();
                return;
            }
        }
        // Validate omzet for non-PPJ
        elseif (!$this->omzet || $this->omzet <= 0) {
            Notification::make()->warning()->title('Masukkan omzet yang valid (lebih dari 0)')->send();
            return;
        }

        if (!$this->wajibPajakData) {
            Notification::make()->warning()->title('Data wajib pajak tidak ditemukan untuk objek ini')->send();
            return;
        }

        $instansi = $this->resolveSelectedInstansi();
        if ($this->shouldShowInstansiField() && filled($this->instansiId) && !$instansi) {
            Notification::make()->warning()->title('Instansi yang dipilih tidak valid atau tidak aktif')->send();
            return;
        }

        $isMultiBilling = $this->selectedTaxObjectData['is_multi_billing'] ?? false;

        // Multi-billing objects (OPD/insidentil): validate keterangan, skip duplicate check
        if ($isMultiBilling) {
            if (empty(trim($this->keterangan ?? ''))) {
                Notification::make()->warning()->title('Keterangan wajib diisi untuk objek pajak ini')->send();
                return;
            }
            if (strlen(trim($this->keterangan)) < 5) {
                Notification::make()->warning()->title('Keterangan minimal 5 karakter')->send();
                return;
            }
            $this->existingBillingInfo = null;
            $this->doGenerateBilling();
            return;
        }

        $skippedPeriodInfo = $this->getSkippedPeriodInfo();

        if ($skippedPeriodInfo && !$this->skipMonthWarningAcknowledged) {
            $this->skippedMonthConfirmTitle = 'Masa Pajak Sebelumnya Belum Dibuat';
            $this->skippedMonthConfirmMessage = "Masa pajak <strong>{$skippedPeriodInfo['missing_label']}</strong> belum dibuat, tetapi Anda memilih <strong>{$skippedPeriodInfo['selected_label']}</strong>. Sistem tetap mengizinkan lanjut. Apakah Anda ingin menerbitkan billing untuk periode yang dipilih?";
            $this->showSkippedMonthConfirm = true;

            return;
        }

        $existingTax = app(BillingService::class)->findExistingBillingForPeriod(
            $this->selectedTaxObjectId,
            $this->masaPajakBulan,
            $this->masaPajakTahun,
        );

        if ($existingTax) {
            $periodLabel = Carbon::create($this->masaPajakTahun, $this->masaPajakBulan, 1)
                ->translatedFormat('F Y');

            $this->existingBillingInfo = $this->buildExistingBillingInfo($existingTax, $periodLabel);

            if ($this->existingBillingInfo['is_paid']) {
                $n = app(BillingService::class)->resolveRevisionContext(
                    $existingTax,
                    $this->selectedTaxObjectId,
                    $this->masaPajakBulan,
                    $this->masaPajakTahun,
                )['pembetulan_ke'];
                $this->duplicateConfirmTitle   = 'Masa Pajak Sudah Lunas — Buat Pembetulan?';
                $this->duplicateConfirmMessage = "Masa pajak <strong>{$periodLabel}</strong> sudah memiliki Kode Pembayaran Aktif "
                    . "<strong>{$existingTax->billing_code}</strong> yang telah dibayar/diverifikasi. "
                    . "Billing baru akan dibuat sebagai <strong>Pembetulan ke-{$n}</strong>. Lanjutkan?";
            } else {
                $this->duplicateConfirmTitle   = 'Billing Sudah Ada — Batalkan &amp; Ganti?';
                $this->duplicateConfirmMessage = "Terdapat Kode Pembayaran Aktif <strong>{$existingTax->billing_code}</strong> "
                    . "untuk masa pajak <strong>{$periodLabel}</strong> dengan status <em>{$this->existingBillingInfo['status_label']}</em>. "
                    . "Billing lama akan <strong>dibatalkan</strong> dan digantikan yang baru. Lanjutkan?";
            }

            $this->showDuplicateConfirm = true;
            return;
        }

        $this->existingBillingInfo = null;
        $this->doGenerateBilling();
    }

    public function confirmAndGenerate(): void
    {
        $this->showDuplicateConfirm = false;
        $this->doGenerateBilling();
    }

    public function confirmSkippedMonthAndContinue(): void
    {
        $this->showSkippedMonthConfirm = false;
        $this->skipMonthWarningAcknowledged = true;

        $this->terbitkanBilling();
    }

    public function cancelDuplicateConfirm(): void
    {
        $this->showDuplicateConfirm = false;
    }

    public function cancelSkippedMonthConfirm(): void
    {
        $this->showSkippedMonthConfirm = false;
        $this->skipMonthWarningAcknowledged = false;
    }

    private function doGenerateBilling(): void
    {
        $this->normalizePpjDecimalInputs();

        if (!$this->wajibPajakData || !$this->selectedTaxObjectData) {
            Notification::make()->warning()->title('Data tidak lengkap')->send();
            return;
        }

        // Validate required input based on type
        $subJenisKode = $this->selectedTaxObjectData['sub_jenis_kode'] ?? null;
        if ($subJenisKode === 'PPJ_SUMBER_LAIN') {
            if (!$this->ppjPokokPajak || $this->ppjPokokPajak <= 0) {
                Notification::make()->warning()->title('Pokok pajak terutang harus lebih dari 0')->send();
                return;
            }
        } elseif ($subJenisKode === 'PPJ_DIHASILKAN_SENDIRI') {
            if (!$this->ppjKapasitasKva || !$this->ppjHargaSatuan) {
                Notification::make()->warning()->title('Lengkapi data komponen PPJ')->send();
                return;
            }
        } elseif (!$this->omzet || $this->omzet <= 0) {
            Notification::make()->warning()->title('Data tidak lengkap')->send();
            return;
        }

        try {
            $instansi = $this->resolveSelectedInstansi();

            $billingService = app(BillingService::class);
            $existingTax = $this->existingBillingInfo
                ? Tax::find($this->existingBillingInfo['id'])
                : null;
            $revisionContext = $billingService->resolveRevisionContext(
                $existingTax,
                $this->selectedTaxObjectId,
                $this->masaPajakBulan,
                $this->masaPajakTahun,
            );

            $pembetulanKe = $revisionContext['pembetulan_ke'];
            $revisionAttemptNo = $revisionContext['revision_attempt_no'];
            $notesPrefix  = $revisionContext['notes_prefix'];
            $parentTaxId  = $revisionContext['parent_tax_id'];

            if ($existingTax && !$this->existingBillingInfo['is_paid']) {
                $billingService->cancelAndArchiveBilling($existingTax);
            }

            // Calculate billing_sequence for multi-billing objects (OPD/insidentil)
            $isMultiBilling = $this->selectedTaxObjectData['is_multi_billing'] ?? false;
            $billingSequence = 0;
            if ($isMultiBilling) {
                $billingSequence = $billingService->getNextBillingSequence(
                    $this->selectedTaxObjectId,
                    $this->masaPajakBulan,
                    $this->masaPajakTahun,
                );
            }

            // Build notes
            $keteranganNotes = $isMultiBilling && !empty($this->keterangan)
                ? trim($this->keterangan) . '. '
                : '';

            $notesAll = $keteranganNotes . $notesPrefix . 'Dibuat oleh petugas: '
                . (auth()->user()->nama_lengkap ?? auth()->user()->name);

            $subJenisKode = $this->selectedTaxObjectData['sub_jenis_kode'] ?? null;

            // Branch: PPJ Sumber Lain (PLN)
            if ($subJenisKode === 'PPJ_SUMBER_LAIN') {
                /** @var PpjService $ppjService */
                $ppjService = app(PpjService::class);
                $tax = $ppjService->generateBillingPpjSumberLain([
                    'jenis_pajak_id'     => $this->selectedTaxObjectData['jenis_pajak_id'],
                    'sub_jenis_pajak_id' => $this->selectedTaxObjectData['sub_jenis_pajak_id'],
                    'tax_object_id'      => $this->selectedTaxObjectId,
                    'user_id'            => $this->wajibPajakData['user_id'],
                    'pokok_pajak'        => $this->ppjPokokPajak,
                    'tarif_persen'       => $this->selectedTaxObjectData['tarif_persen'],
                    'bulan'              => $this->masaPajakBulan,
                    'tahun'              => $this->masaPajakTahun,
                    'pembetulan_ke'      => $pembetulanKe,
                    'revision_attempt_no' => $revisionAttemptNo,
                    'billing_sequence'   => $billingSequence,
                    'parent_tax_id'      => $parentTaxId,
                    'notes'              => $notesAll,
                ]);
            }
            // Branch: PPJ Dihasilkan Sendiri (Non PLN)
            elseif ($subJenisKode === 'PPJ_DIHASILKAN_SENDIRI') {
                /** @var PpjService $ppjService */
                $ppjService = app(PpjService::class);
                $tax = $ppjService->generateBillingPpjNonPln([
                    'jenis_pajak_id'          => $this->selectedTaxObjectData['jenis_pajak_id'],
                    'sub_jenis_pajak_id'      => $this->selectedTaxObjectData['sub_jenis_pajak_id'],
                    'tax_object_id'           => $this->selectedTaxObjectId,
                    'user_id'                 => $this->wajibPajakData['user_id'],
                    'kapasitas_kva'           => $this->ppjKapasitasKva,
                    'tingkat_penggunaan_persen' => $this->ppjTingkatPenggunaanPersen,
                    'jangka_waktu_jam'        => $this->ppjJangkaWaktuJam,
                    'harga_satuan'            => $this->ppjHargaSatuan,
                    'harga_satuan_listrik_id' => $this->ppjHargaSatuanListrikId,
                    'tarif_persen'            => $this->selectedTaxObjectData['tarif_persen'],
                    'bulan'                   => $this->masaPajakBulan,
                    'tahun'                   => $this->masaPajakTahun,
                    'pembetulan_ke'           => $pembetulanKe,
                    'revision_attempt_no'     => $revisionAttemptNo,
                    'billing_sequence'        => $billingSequence,
                    'parent_tax_id'           => $parentTaxId,
                    'notes'                   => $notesAll,
                ]);
            }
            // Default: standard self-assessment (omzet × tarif)
            else {
                $tax = app(BillingService::class)->generateBillingByPetugas([
                    'jenis_pajak_id'     => $this->selectedTaxObjectData['jenis_pajak_id'],
                    'sub_jenis_pajak_id' => $this->selectedTaxObjectData['sub_jenis_pajak_id'],
                    'tax_object_id'      => $this->selectedTaxObjectId,
                    'user_id'            => $this->wajibPajakData['user_id'],
                    ...($instansi?->toTransactionAttributes() ?? []),
                    'omzet'              => $this->omzet,
                    'tarif_persen'       => $this->selectedTaxObjectData['tarif_persen'],
                    'bulan'              => $this->masaPajakBulan,
                    'tahun'              => $this->masaPajakTahun,
                    'pembetulan_ke'      => $pembetulanKe,
                    'revision_attempt_no' => $revisionAttemptNo,
                    'billing_sequence'   => $billingSequence,
                    'parent_tax_id'      => $parentTaxId,
                    'notes'              => $notesAll,
                ]);
            }

            // In-app notification to WP
            $wpUser = User::find($this->wajibPajakData['user_id']);
            if ($wpUser) {
                $periodLabel = Carbon::create($this->masaPajakTahun, $this->masaPajakBulan, 1)
                    ->translatedFormat('F Y');
                $expiredAt = $tax->payment_expired_at
                    ? Carbon::parse($tax->payment_expired_at)->format('d/m/Y') : '-';

                Notification::make()
                    ->title('Billing Baru Diterbitkan')
                    ->body("Kode Pembayaran Aktif {$tax->billing_code} untuk {$periodLabel} sudah diterbitkan "
                        . "oleh petugas. Silakan lakukan pembayaran sebelum {$expiredAt}.")
                    ->sendToDatabase($wpUser);
            }

            $this->billingResult = [
                'tax_id'          => $tax->id,
                'billing_code'    => $tax->billing_code,
                'amount'          => (float) $tax->amount,
                'omzet'           => (float) $tax->omzet,
                'tarif'           => $this->selectedTaxObjectData['tarif_persen'],
                'expired_at'      => $tax->payment_expired_at
                    ? Carbon::parse($tax->payment_expired_at)->format('d/m/Y') : '-',
                'masa_pajak'      => Carbon::create($this->masaPajakTahun, $this->masaPajakBulan, 1)
                    ->translatedFormat('F Y'),
                'nama_wp'         => $this->wajibPajakData['nama_lengkap'],
                'nama_objek'      => $this->selectedTaxObjectData['nama'],
                'jenis_pajak'     => $this->selectedTaxObjectData['jenis_pajak_nama'],
                'instansi'        => $tax->instansi_nama,
                'pembetulan_ke'   => $pembetulanKe,
                'is_tambahan'     => $pembetulanKe > 0,
                'sub_jenis_kode'  => $subJenisKode,
                'is_ppj'          => in_array($subJenisKode, ['PPJ_SUMBER_LAIN', 'PPJ_DIHASILKAN_SENDIRI']),
            ];

            $title = $pembetulanKe > 0 ? 'Billing Tambahan Berhasil' : 'Billing Berhasil Dibuat';
            Notification::make()->success()->title($title)->body("Kode Pembayaran Aktif: {$tax->billing_code}")->send();

        } catch (Exception $e) {
            Notification::make()->danger()->title('Gagal membuat billing')->body($e->getMessage())->send();
        }
    }

    // ── Reset ────────────────────────────────────────────────────────────────

    /**
     * Reset right panel only — keeps the current search results visible.
     */
    public function buatBaru(): void
    {
        $this->selectedTaxObjectId   = null;
        $this->selectedTaxObjectData = null;
        $this->wajibPajakData        = null;
        $this->omzet                 = null;
        $this->masaPajakBulan        = (int) date('n');
        $this->masaPajakTahun        = (int) date('Y');
        $this->existingBillingInfo   = null;
        $this->billingResult         = null;
        $this->showDuplicateConfirm  = false;
        $this->showSkippedMonthConfirm = false;
        $this->skipMonthWarningAcknowledged = false;
        $this->keterangan            = null;
        $this->instansiId            = null;

        // Reset PPJ fields
        $this->ppjPokokPajak              = null;
        $this->ppjKapasitasKva            = null;
        $this->ppjTingkatPenggunaanPersen = null;
        $this->ppjJangkaWaktuJam          = null;
        $this->ppjHargaSatuanListrikId    = null;
        $this->ppjHargaSatuan             = null;
        $this->ppjHargaSatuanOptions      = [];
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function mapTaxObject($obj): array
    {
        $next = app(BillingService::class)->getNextMasaPajak($obj->id);

        return [
            'id'               => $obj->id,
            'nama'             => $obj->nama_objek_pajak,
            'alamat'           => $obj->alamat_objek,
            'npwpd'            => $obj->npwpd,
            'nopd'             => $obj->nopd,
            'nik_hash'         => $obj->nik_hash,
            'sub_jenis'        => $obj->subJenisPajak?->nama ?? '-',
            'jenis_pajak_nama' => $obj->jenisPajak?->nama ?? '-',
            'tarif_persen'     => TarifPajak::lookup($obj->sub_jenis_pajak_id) ?? (float) $obj->tarif_persen,
            'jenis_pajak_id'   => $obj->jenis_pajak_id,
            'sub_jenis_pajak_id' => $obj->sub_jenis_pajak_id,
            'next_bulan'       => $next['bulan'],
            'next_tahun'       => $next['tahun'],
            'next_label'       => $next['label'],
            'is_new'           => $next['isNew'],
            'is_opd'           => (bool) $obj->is_opd,
            'is_insidentil'    => (bool) $obj->is_insidentil,
            'is_multi_billing' => $obj->isMultiBilling(),
            'sub_jenis_kode'   => $obj->subJenisPajak?->kode ?? null,
        ];
    }

    public function getPopularTags(): array
    {
        return JenisPajak::whereIn('kode', ['41101', '41102', '41103', '41105', '41107'])
            ->where('is_active', true)
            ->orderBy('urutan')
            ->pluck('nama')
            ->toArray();
    }

    public function shouldShowInstansiField(): bool
    {
        return (bool) ($this->selectedTaxObjectData['is_opd'] ?? false);
    }

    private function loadInstansiOptions(): void
    {
        $this->instansiOptions = Instansi::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn (Instansi $instansi) => [
                $instansi->id => $instansi->nama . ' (' . ($instansi->kategori?->getLabel() ?? '-') . ')',
            ])
            ->toArray();
    }

    private function resolveSelectedInstansi(): ?Instansi
    {
        if (! $this->shouldShowInstansiField() || blank($this->instansiId)) {
            return null;
        }

        return Instansi::query()
            ->where('is_active', true)
            ->find($this->instansiId);
    }

    public function updatedMasaPajakBulan(): void
    {
        $this->resetSkippedMonthWarningState();
    }

    public function updatedMasaPajakTahun(): void
    {
        $this->resetSkippedMonthWarningState();
    }

    private function getSkippedPeriodInfo(): ?array
    {
        if (!$this->selectedTaxObjectData || ($this->selectedTaxObjectData['is_multi_billing'] ?? false)) {
            return null;
        }

        $expectedMonth = $this->selectedTaxObjectData['next_bulan'] ?? null;
        $expectedYear = $this->selectedTaxObjectData['next_tahun'] ?? null;

        if (!$expectedMonth || !$expectedYear || !$this->masaPajakBulan || !$this->masaPajakTahun) {
            return null;
        }

        $expectedPeriod = Carbon::create((int) $expectedYear, (int) $expectedMonth, 1)->startOfMonth();
        $selectedPeriod = Carbon::create((int) $this->masaPajakTahun, (int) $this->masaPajakBulan, 1)->startOfMonth();

        if ($selectedPeriod->lessThanOrEqualTo($expectedPeriod)) {
            return null;
        }

        return [
            'missing_label' => $expectedPeriod->translatedFormat('F Y'),
            'selected_label' => $selectedPeriod->translatedFormat('F Y'),
        ];
    }

    private function resetSkippedMonthWarningState(): void
    {
        $this->showSkippedMonthConfirm = false;
        $this->skipMonthWarningAcknowledged = false;
        $this->skippedMonthConfirmTitle = '';
        $this->skippedMonthConfirmMessage = '';
    }

    public static function getBadgeColor(string $jenisNama): string
    {
        $map = [
            'hotel'      => 'blue',
            'restoran'   => 'emerald',
            'parkir'     => 'amber',
            'hiburan'    => 'purple',
            'penerangan' => 'sky',
            'air'        => 'cyan',
            'reklame'    => 'rose',
        ];

        $lower = strtolower($jenisNama);
        foreach ($map as $key => $color) {
            if (str_contains($lower, $key)) return $color;
        }

        return 'gray';
    }
}

