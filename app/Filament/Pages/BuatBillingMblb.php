<?php

namespace App\Filament\Pages;

use Exception;
use App\Domain\Master\Models\JenisPajak;
use App\Enums\TaxStatus;
use App\Filament\Pages\Concerns\InteractsWithDuplicateBillingInfo;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Tax\Services\BillingService;
use App\Domain\Tax\Services\MblbService;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Auth\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;

class BuatBillingMblb extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithDuplicateBillingInfo;

    protected static string | \BackedEnum | null $navigationIcon  = 'heroicon-o-cube';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Petugas';
    protected static ?string $navigationLabel = 'Buat Billing MBLB';
    protected static ?string $title           = 'Buat Billing MBLB';
    protected static ?int    $navigationSort  = 4;
    protected string  $view            = 'filament.pages.buat-billing-mblb';

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
    public array   $mineralItems          = [];
    public ?int    $masaPajakBulan        = null;
    public ?int    $masaPajakTahun        = null;
    public ?string $keterangan            = null;

    // ── State: Tax config ───────────────────────────────────────────────────
    public float   $tarifPersen  = 20;
    public float   $opsenPersen  = 25;

    // ── State: Duplicate confirmation modal ─────────────────────────────────
    public ?array  $existingBillingInfo    = null;
    public bool    $showDuplicateConfirm   = false;
    public string  $duplicateConfirmTitle  = '';
    public string  $duplicateConfirmMessage = '';

    // ── State: Success panel (right) ────────────────────────────────────────
    public ?array $billingResult = null;

    // ────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->masaPajakBulan = (int) date('n');
        $this->masaPajakTahun = (int) date('Y');
        $this->loadMineralItems();
        $this->loadTarifConfig();
    }

    private function loadMineralItems(): void
    {
        $minerals = app(MblbService::class)->getAllMineralItems();
        $this->mineralItems = $minerals->map(fn ($m) => [
            'id' => $m->id,
            'nama_mineral' => $m->nama_mineral,
            'nama_alternatif' => $m->nama_alternatif ?? [],
            'harga_patokan' => (float) $m->harga_patokan,
            'satuan' => $m->satuan,
            'volume' => null,
        ])->toArray();
    }

    private function loadTarifConfig(): void
    {
        $jenisPajak = JenisPajak::where('kode', '41106')->first();
        if ($jenisPajak) {
            $this->tarifPersen = (float) ($jenisPajak->tarif_default ?? 20);
            $this->opsenPersen = (float) ($jenisPajak->opsen_persen ?? 25);
        }
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
            $mblbJenisPajakIds = JenisPajak::where('kode', '41106')->pluck('id');

            // NIK — all-digit ≥ 5 chars
            if (ctype_digit($keyword) && strlen($keyword) >= 5) {
                $nikHash = WajibPajak::generateHash($keyword);
                $results = TaxObject::where('nik_hash', $nikHash)
                    ->where('is_active', true)
                    ->whereIn('jenis_pajak_id', $mblbJenisPajakIds)
                    ->with(['subJenisPajak', 'jenisPajak'])
                    ->get()
                    ->map(fn ($obj) => $this->mapTaxObject($obj))
                    ->toArray();
            }

            // NPWPD / nama / NIK (decrypt + filter)
            if (empty($results)) {
                $kw = strtolower($keyword);
                $results = TaxObject::where('is_active', true)
                    ->whereIn('jenis_pajak_id', $mblbJenisPajakIds)
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

    public function toggleDetail(string $id): void
    {
        $this->expandedDetailId = $this->expandedDetailId === $id ? null : $id;
    }

    // ── Object Selection ────────────────────────────────────────────────────

    public function selectObject(string $id): void
    {
        $selected = collect($this->searchResults)->firstWhere('id', $id);
        if (!$selected) return;

        $this->selectedTaxObjectId   = $id;
        $this->selectedTaxObjectData = $selected;
        $this->masaPajakBulan        = $selected['next_bulan'];
        $this->masaPajakTahun        = $selected['next_tahun'];
        $this->existingBillingInfo   = null;
        $this->billingResult         = null;
        $this->showDuplicateConfirm  = false;
        $this->keterangan            = null;

        // Reset volume on all minerals
        foreach ($this->mineralItems as $key => $item) {
            $this->mineralItems[$key]['volume'] = null;
        }

        // Load WP data
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

    public function terbitkanBilling(): void
    {
        // Validate at least one mineral has volume > 0
        $hasVolume = collect($this->mineralItems)->contains(fn ($item) => ((float) ($item['volume'] ?? 0)) > 0);
        if (!$hasVolume) {
            Notification::make()->warning()->title('Masukkan volume minimal satu jenis mineral')->send();
            return;
        }

        // Validate volumes max 2 decimal places
        foreach ($this->mineralItems as $item) {
            $vol = $item['volume'] ?? 0;
            if ($vol > 0 && !preg_match('/^\d+(\.\d{1,2})?$/', (string) $vol)) {
                Notification::make()->warning()->title('Volume maksimal 2 digit desimal')->send();
                return;
            }
        }

        if (!$this->wajibPajakData) {
            Notification::make()->warning()->title('Data wajib pajak tidak ditemukan untuk objek ini')->send();
            return;
        }

        $isMultiBilling = $this->selectedTaxObjectData['is_multi_billing'] ?? false;

        // Multi-billing objects (OPD/insidentil): validate keterangan
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

        // Check duplicate
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
                $n = $this->existingBillingInfo['pembetulan_ke'] + 1;
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

    public function cancelDuplicateConfirm(): void
    {
        $this->showDuplicateConfirm = false;
    }

    private function doGenerateBilling(): void
    {
        if (!$this->wajibPajakData || !$this->selectedTaxObjectData) {
            Notification::make()->warning()->title('Data tidak lengkap')->send();
            return;
        }

        try {
            // Prepare mineral items for calculation
            $items = collect($this->mineralItems)->map(fn ($item) => [
                'harga_patokan_mblb_id' => $item['id'],
                'jenis_mblb' => $item['nama_mineral'],
                'volume' => (float) ($item['volume'] ?? 0),
                'harga_patokan' => (float) $item['harga_patokan'],
            ])->toArray();

            /** @var MblbService $mblbService */
            $mblbService = app(MblbService::class);
            $calculation = $mblbService->calculateTax($items, $this->tarifPersen, $this->opsenPersen);

            if (empty($calculation['details'])) {
                Notification::make()->warning()->title('Tidak ada mineral dengan volume > 0')->send();
                return;
            }

            $pembetulanKe = 0;
            $notesPrefix  = '';
            $parentTaxId  = null;

            if ($this->existingBillingInfo) {
                if ($this->existingBillingInfo['is_paid']) {
                    $pembetulanKe = $this->existingBillingInfo['pembetulan_ke'] + 1;
                    $notesPrefix  = "Pembetulan ke-{$pembetulanKe} atas billing {$this->existingBillingInfo['billing_code']}. ";
                    $parentTaxId  = $this->existingBillingInfo['id'];
                } else {
                    $oldTax = Tax::find($this->existingBillingInfo['id']);
                    if ($oldTax) {
                        $oldTax->update([
                            'status' => TaxStatus::Cancelled,
                            'cancelled_at' => now(),
                            'cancelled_by' => auth()->id(),
                            'cancellation_reason' => 'Digantikan oleh billing baru',
                        ]);
                        $oldTax->delete();
                    }
                    $notesPrefix = "Pengganti billing {$this->existingBillingInfo['billing_code']}. ";
                }
            }

            // Calculate billing_sequence for multi-billing
            $isMultiBilling = $this->selectedTaxObjectData['is_multi_billing'] ?? false;
            $billingSequence = 0;
            if ($isMultiBilling) {
                $billingSequence = app(BillingService::class)->getNextBillingSequence(
                    $this->selectedTaxObjectId,
                    $this->masaPajakBulan,
                    $this->masaPajakTahun,
                );
            }

            // WAPU: notes = keterangan input petugas saja
            // Non-WAPU: notes = "Dibuat oleh petugas: ..."
            if ($isMultiBilling && !empty($this->keterangan)) {
                $notes = $notesPrefix . trim($this->keterangan);
            } else {
                $notes = $notesPrefix . 'Dibuat oleh petugas: '
                    . (auth()->user()->nama_lengkap ?? auth()->user()->name);
            }

            $tax = $mblbService->generateBilling([
                'jenis_pajak_id'     => $this->selectedTaxObjectData['jenis_pajak_id'],
                'sub_jenis_pajak_id' => $this->selectedTaxObjectData['sub_jenis_pajak_id'],
                'tax_object_id'      => $this->selectedTaxObjectId,
                'user_id'            => $this->wajibPajakData['user_id'],
                'total_dpp'          => $calculation['total_dpp'],
                'pokok_pajak'        => $calculation['pokok_pajak'],
                'opsen'              => $calculation['opsen'],
                'tarif_persen'       => $this->tarifPersen,
                'bulan'              => $this->masaPajakBulan,
                'tahun'              => $this->masaPajakTahun,
                'pembetulan_ke'      => $pembetulanKe,
                'billing_sequence'   => $billingSequence,
                'parent_tax_id'      => $parentTaxId,
                'notes'              => $notes,
                'details'            => $calculation['details'],
            ]);

            // In-app notification to WP
            $wpUser = User::find($this->wajibPajakData['user_id']);
            if ($wpUser) {
                $periodLabel = Carbon::create($this->masaPajakTahun, $this->masaPajakBulan, 1)
                    ->translatedFormat('F Y');
                $expiredAt = $tax->payment_expired_at
                    ? Carbon::parse($tax->payment_expired_at)->format('d/m/Y') : '-';

                Notification::make()
                    ->title('Billing MBLB Baru Diterbitkan')
                    ->body("Kode Pembayaran Aktif {$tax->billing_code} untuk {$periodLabel} sudah diterbitkan "
                        . "oleh petugas. Silakan lakukan pembayaran sebelum {$expiredAt}.")
                    ->sendToDatabase($wpUser);
            }

            $this->billingResult = [
                'tax_id'        => $tax->id,
                'billing_code'  => $tax->billing_code,
                'amount'        => $calculation['pokok_pajak'],
                'opsen'         => $calculation['opsen'],
                'total'         => $calculation['total'],
                'total_dpp'     => $calculation['total_dpp'],
                'tarif'         => $this->tarifPersen,
                'opsen_persen'  => $this->opsenPersen,
                'expired_at'    => $tax->payment_expired_at
                    ? Carbon::parse($tax->payment_expired_at)->format('d/m/Y') : '-',
                'masa_pajak'    => Carbon::create($this->masaPajakTahun, $this->masaPajakBulan, 1)
                    ->translatedFormat('F Y'),
                'nama_wp'       => $this->wajibPajakData['nama_lengkap'],
                'nama_objek'    => $this->selectedTaxObjectData['nama'],
                'jenis_pajak'   => 'MBLB',
                'pembetulan_ke' => $pembetulanKe,
                'is_tambahan'   => $pembetulanKe > 0,
                'detail_count'  => count($calculation['details']),
            ];

            $title = $pembetulanKe > 0 ? 'Billing Tambahan Berhasil' : 'Billing MBLB Berhasil Dibuat';
            Notification::make()->success()->title($title)->body("Kode Pembayaran Aktif: {$tax->billing_code}")->send();

        } catch (Exception $e) {
            Notification::make()->danger()->title('Gagal membuat billing')->body($e->getMessage())->send();
        }
    }

    // ── Reset ────────────────────────────────────────────────────────────────

    public function buatBaru(): void
    {
        $this->selectedTaxObjectId   = null;
        $this->selectedTaxObjectData = null;
        $this->wajibPajakData        = null;
        $this->masaPajakBulan        = (int) date('n');
        $this->masaPajakTahun        = (int) date('Y');
        $this->existingBillingInfo   = null;
        $this->billingResult         = null;
        $this->showDuplicateConfirm  = false;
        $this->keterangan            = null;
        $this->loadMineralItems();
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
            'jenis_pajak_nama' => $obj->jenisPajak?->nama ?? 'MBLB',
            'tarif_persen'     => (float) $obj->tarif_persen,
            'jenis_pajak_id'   => $obj->jenis_pajak_id,
            'sub_jenis_pajak_id' => $obj->sub_jenis_pajak_id,
            'next_bulan'       => $next['bulan'],
            'next_tahun'       => $next['tahun'],
            'next_label'       => $next['label'],
            'is_new'           => $next['isNew'],
            'is_opd'           => (bool) $obj->is_opd,
            'is_insidentil'    => (bool) $obj->is_insidentil,
            'is_multi_billing' => $obj->isMultiBilling(),
        ];
    }
}
