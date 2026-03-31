<?php

namespace App\Filament\Pages;

use Exception;
use App\Domain\Master\Models\JenisPajak;
use App\Enums\TaxStatus;
use App\Filament\Pages\Concerns\InteractsWithDuplicateBillingInfo;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use App\Domain\Tax\Services\BillingService;
use App\Domain\Tax\Services\SarangWaletService;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Auth\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;

class BuatBillingSarangWalet extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithDuplicateBillingInfo;

    protected static string | \BackedEnum | null $navigationIcon  = 'heroicon-o-home';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Petugas';
    protected static ?string $navigationLabel = 'Buat Billing Sarang Walet';
    protected static ?string $title           = 'Buat Billing Sarang Walet';
    protected static ?int    $navigationSort  = 5;
    protected string  $view            = 'filament.pages.buat-billing-sarang-walet';

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

    // ── State: Search panel ─────────────────────────────────────────────────
    public ?string $searchKeyword    = null;
    public array   $searchResults   = [];
    public ?string $expandedDetailId = null;

    // ── State: Form panel ───────────────────────────────────────────────────
    public ?string $selectedTaxObjectId   = null;
    public ?array  $selectedTaxObjectData = null;
    public ?array  $wajibPajakData        = null;
    public array   $jenisSarangItems      = [];
    public ?string $selectedJenisSarangId = null;
    public ?float  $volumeKg             = null;
    public ?int    $masaPajakTahun        = null;

    // ── State: Tax config ───────────────────────────────────────────────────
    public float   $tarifPersen  = 10;

    // ── State: Duplicate confirmation modal ─────────────────────────────────
    public ?array  $existingBillingInfo    = null;
    public bool    $showDuplicateConfirm   = false;
    public string  $duplicateConfirmTitle  = '';
    public string  $duplicateConfirmMessage = '';

    // ── State: Success panel ────────────────────────────────────────────────
    public ?array $billingResult = null;

    public function mount(): void
    {
        $this->masaPajakTahun = (int) date('Y');
        $this->loadJenisSarangItems();
        $this->loadTarifConfig();
    }

    private function loadJenisSarangItems(): void
    {
        $items = app(SarangWaletService::class)->getAllJenisSarang();
        $this->jenisSarangItems = $items->map(fn ($js) => [
            'id' => $js->id,
            'nama_jenis' => $js->nama_jenis,
            'harga_patokan' => (float) $js->harga_patokan,
            'satuan' => $js->satuan,
        ])->toArray();
    }

    private function loadTarifConfig(): void
    {
        $jenisPajak = JenisPajak::where('kode', '41109')->first();
        if ($jenisPajak) {
            $this->tarifPersen = (float) ($jenisPajak->tarif_default ?? 10);
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
            $swJenisPajakIds = JenisPajak::where('kode', '41109')->pluck('id');

            if (ctype_digit($keyword) && strlen($keyword) >= 5) {
                $nikHash = WajibPajak::generateHash($keyword);
                $results = TaxObject::where('nik_hash', $nikHash)
                    ->where('is_active', true)
                    ->whereIn('jenis_pajak_id', $swJenisPajakIds)
                    ->with(['subJenisPajak', 'jenisPajak'])
                    ->get()
                    ->map(fn ($obj) => $this->mapTaxObject($obj))
                    ->toArray();
            }

            if (empty($results)) {
                $kw = strtolower($keyword);
                $results = TaxObject::where('is_active', true)
                    ->whereIn('jenis_pajak_id', $swJenisPajakIds)
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
        $this->masaPajakTahun        = $selected['next_tahun'];
        $this->selectedJenisSarangId = null;
        $this->volumeKg              = null;
        $this->existingBillingInfo   = null;
        $this->billingResult         = null;
        $this->showDuplicateConfirm  = false;

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
        if (!$this->selectedJenisSarangId) {
            Notification::make()->warning()->title('Pilih jenis sarang terlebih dahulu')->send();
            return;
        }

        $vol = (float) ($this->volumeKg ?? 0);
        if ($vol <= 0) {
            Notification::make()->warning()->title('Masukkan volume (kg)')->send();
            return;
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', (string) $vol)) {
            Notification::make()->warning()->title('Volume maksimal 2 digit desimal')->send();
            return;
        }

        if (!$this->wajibPajakData) {
            Notification::make()->warning()->title('Data wajib pajak tidak ditemukan untuk objek ini')->send();
            return;
        }

        // Check duplicate per tahun
        $existingTax = Tax::where('tax_object_id', $this->selectedTaxObjectId)
            ->where('masa_pajak_tahun', $this->masaPajakTahun)
            ->whereIn('status', TaxStatus::activeStatuses())
            ->orderByDesc('pembetulan_ke')
            ->first();

        if ($existingTax) {
            $periodLabel = 'Tahun ' . $this->masaPajakTahun;
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
            $jenisSarang = collect($this->jenisSarangItems)->firstWhere('id', $this->selectedJenisSarangId);
            if (!$jenisSarang) {
                Notification::make()->warning()->title('Jenis sarang tidak valid')->send();
                return;
            }

            $hargaPatokan = (float) $jenisSarang['harga_patokan'];
            $vol = (float) $this->volumeKg;

            /** @var SarangWaletService $service */
            $service = app(SarangWaletService::class);
            $calculation = $service->calculateTax($hargaPatokan, $vol, $this->tarifPersen);

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

            $notes = $notesPrefix . 'Dibuat oleh petugas: '
                . (auth()->user()->nama_lengkap ?? auth()->user()->name);

            $tax = $service->generateBillingByPetugas([
                'jenis_pajak_id'     => $this->selectedTaxObjectData['jenis_pajak_id'],
                'sub_jenis_pajak_id' => $this->selectedTaxObjectData['sub_jenis_pajak_id'],
                'tax_object_id'      => $this->selectedTaxObjectId,
                'user_id'            => $this->wajibPajakData['user_id'],
                'pokok_pajak'        => $calculation['pokok_pajak'],
                'dpp'                => $calculation['dpp'],
                'tarif_persen'       => $this->tarifPersen,
                'bulan'              => null,
                'tahun'              => $this->masaPajakTahun,
                'harga_patokan_sarang_walet_id' => $jenisSarang['id'],
                'jenis_sarang'       => $jenisSarang['nama_jenis'],
                'volume_kg'          => $vol,
                'harga_patokan'      => $hargaPatokan,
                'pembetulan_ke'      => $pembetulanKe,
                'parent_tax_id'      => $parentTaxId,
                'notes'              => $notes,
            ]);

            // In-app notification to WP
            $wpUser = User::find($this->wajibPajakData['user_id']);
            if ($wpUser) {
                $periodLabel = 'Tahun ' . $this->masaPajakTahun;
                $expiredAt = $tax->payment_expired_at
                    ? Carbon::parse($tax->payment_expired_at)->format('d/m/Y') : '-';

                Notification::make()
                    ->title('Billing Sarang Walet Baru Diterbitkan')
                    ->body("Kode Pembayaran Aktif {$tax->billing_code} untuk {$periodLabel} sudah diterbitkan "
                        . "oleh petugas. Silakan lakukan pembayaran sebelum {$expiredAt}.")
                    ->sendToDatabase($wpUser);
            }

            $this->billingResult = [
                'tax_id'        => $tax->id,
                'billing_code'  => $tax->billing_code,
                'amount'        => $calculation['pokok_pajak'],
                'total'         => $calculation['total'],
                'dpp'           => $calculation['dpp'],
                'tarif'         => $this->tarifPersen,
                'jenis_sarang'  => $jenisSarang['nama_jenis'],
                'volume_kg'     => $vol,
                'harga_patokan' => $hargaPatokan,
                'expired_at'    => $tax->payment_expired_at
                    ? Carbon::parse($tax->payment_expired_at)->format('d/m/Y') : '-',
                'masa_pajak'    => 'Tahun ' . $this->masaPajakTahun,
                'nama_wp'       => $this->wajibPajakData['nama_lengkap'],
                'nama_objek'    => $this->selectedTaxObjectData['nama'],
                'jenis_pajak'   => 'Sarang Walet',
                'pembetulan_ke' => $pembetulanKe,
                'is_tambahan'   => $pembetulanKe > 0,
            ];

            $title = $pembetulanKe > 0 ? 'Billing Tambahan Berhasil' : 'Billing Sarang Walet Berhasil Dibuat';
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
        $this->masaPajakTahun        = (int) date('Y');
        $this->selectedJenisSarangId = null;
        $this->volumeKg              = null;
        $this->existingBillingInfo   = null;
        $this->billingResult         = null;
        $this->showDuplicateConfirm  = false;
        $this->loadJenisSarangItems();
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
            'jenis_pajak_nama' => $obj->jenisPajak?->nama ?? 'Sarang Walet',
            'tarif_persen'     => (float) $obj->tarif_persen,
            'jenis_pajak_id'   => $obj->jenis_pajak_id,
            'sub_jenis_pajak_id' => $obj->sub_jenis_pajak_id,
            'next_tahun'       => $next['tahun'],
            'next_label'       => $next['label'] ?? ('Tahun ' . $next['tahun']),
            'is_new'           => $next['isNew'],
        ];
    }
}
