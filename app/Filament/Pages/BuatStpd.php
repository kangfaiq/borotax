<?php

namespace App\Filament\Pages;

use Exception;
use App\Domain\Shared\Services\NotificationService;
use App\Domain\Tax\Models\StpdManual;
use App\Domain\Tax\Models\Tax;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\TaxStatus;
use App\Filament\Resources\StpdManualResource;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class BuatStpd extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon  = 'heroicon-o-document-minus';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Petugas';
    protected static ?string $navigationLabel = 'Buat STPD';
    protected static ?string $title           = 'Buat STPD (Surat Tagihan Pajak Daerah)';
    protected static ?int    $navigationSort  = 6;
    protected string  $view            = 'filament.pages.buat-stpd';

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'petugas', 'verifikator']);
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'petugas', 'verifikator']);
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    // ── State: Search ─────────────────────────────────────────────────────
    public ?string $searchKeyword = null;
    public array $searchResults = [];

    // ── State: Selected Billing ───────────────────────────────────────────
    public ?string $selectedTaxId = null;
    public ?array $taxData = null;
    public ?array $wpData = null;

    // ── State: STPD Form ──────────────────────────────────────────────────
    public ?string $tipeStpd = null; // 'pokok_sanksi' | 'sanksi_saja'
    public ?string $proyeksiTanggalBayar = null;
    public ?string $catatanPetugas = null;

    // ── State: Perhitungan ────────────────────────────────────────────────
    public ?int $hitungBulanTerlambat = null;
    public ?float $hitungSanksi = null;
    public ?float $hitungPokokBelumDibayar = null;
    public ?float $tarifSanksiPersen = null;

    // ── State: Success ────────────────────────────────────────────────────
    public ?array $stpdResult = null;

    // ── Search ────────────────────────────────────────────────────────────

    public function cariBilling(): void
    {
        Tax::syncExpiredStatuses();

        $keyword = trim($this->searchKeyword ?? '');

        if (empty($keyword)) {
            Notification::make()->warning()->title('Masukkan Billing Sumber atau NPWPD.')->send();
            return;
        }

        $this->resetSelection();
        $this->searchResults = [];

        if (strlen($keyword) === 18) {
            // Search by source billing
            $tax = Tax::with(['taxObject', 'jenisPajak', 'subJenisPajak', 'user'])
                ->where('billing_code', $keyword)
                ->first();

            if (!$tax) {
                Notification::make()->danger()->title('Billing tidak ditemukan.')->send();
                return;
            }

            // Valid statuses for STPD: verified (belum bayar), pending, partially_paid (sanksi belum)
            if (!$tax->canCreateManualStpd()) {
                Notification::make()
                    ->warning()
                    ->title('Billing tidak valid untuk STPD')
                    ->body("Status Billing Sumber: " . strtoupper($tax->display_status->value) . ". Hanya billing Pending, Terverifikasi, Kedaluwarsa, Dibayar Sebagian, atau billing Lunas dengan sanksi yang masih tersisa yang dapat dibuatkan STPD.")
                    ->send();
                return;
            }

            $this->selectBilling($tax->id);

        } elseif (strlen($keyword) === 13) {
            // Search by NPWPD
            $wp = WajibPajak::where('npwpd', $keyword)->first();
            if (!$wp) {
                Notification::make()->danger()->title('NPWPD tidak ditemukan.')->send();
                return;
            }

            $taxes = Tax::with(['taxObject', 'jenisPajak', 'subJenisPajak'])
                ->where('user_id', $wp->user_id)
                ->whereIn('status', [TaxStatus::Pending, TaxStatus::Verified, TaxStatus::Expired, TaxStatus::PartiallyPaid, TaxStatus::Paid])
                ->get()
                ->filter(fn (Tax $tax) => $tax->canCreateManualStpd())
                ->values();

            if ($taxes->isEmpty()) {
                Notification::make()->info()->title('Tidak ada tagihan aktif.')->body('Tidak ditemukan tagihan yang memenuhi syarat untuk STPD.')->send();
                return;
            }

            if ($taxes->count() === 1) {
                $this->selectBilling($taxes->first()->id);
            } else {
                $this->searchResults = $taxes->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'billing_code' => $t->billing_code,
                        'nama_objek' => $t->taxObject->nama_objek_pajak ?? '-',
                        'jenis_pajak' => $t->jenisPajak->nama ?? '-',
                        'masa_pajak' => $t->masa_pajak_bulan
                            ? Carbon::create()->month((int) $t->masa_pajak_bulan)->translatedFormat('F') . ' ' . $t->masa_pajak_tahun
                            : ($t->masa_pajak_tahun ?? '-'),
                        'pokok' => (float) $t->amount,
                        'sanksi' => (float) $t->sanksi,
                        'status' => $t->display_status->value,
                        'status_label' => $t->display_status_label,
                        'total_dibayar' => $t->getTotalPaid(),
                        'sisa' => $t->getRemainingAmount(),
                    ];
                })->toArray();
            }
        } else {
            Notification::make()->warning()->title('Format tidak valid')->body('Masukkan 18 digit Billing Sumber atau 13 digit NPWPD.')->send();
        }
    }

    // ── Select Billing ────────────────────────────────────────────────────

    public function selectBilling(string $taxId): void
    {
        $tax = Tax::with(['taxObject', 'jenisPajak', 'subJenisPajak', 'user'])->find($taxId);
        if (!$tax) return;

        // Check existing draft/approved STPD manual for this tax
        $existing = StpdManual::where('tax_id', $taxId)
            ->whereIn('status', ['draft', 'disetujui'])
            ->first();

        if ($existing) {
            $nomorDisplay = $existing->nomor_stpd ?? 'draft';
            Notification::make()
                ->warning()
                ->title('STPD sudah ada')
                ->body("Billing Sumber ini sudah memiliki STPD Manual ({$nomorDisplay}) dengan status: {$existing->status}.")
                ->send();
            return;
        }

        $wp = WajibPajak::where('user_id', $tax->user_id)->first();

        $pokokPajak = (float) $tax->amount;
        $sanksiExisting = (float) $tax->sanksi;
        $totalPrincipalPaid = $tax->getTotalPrincipalPaid();
        $totalPenaltyPaid = $tax->getTotalPenaltyPaid();
        $sanksiBelumDibayar = max(0, $sanksiExisting - $totalPenaltyPaid);
        $pokokBelumDibayar = max(0, $pokokPajak - $totalPrincipalPaid);
        $isPokokLunas = $totalPrincipalPaid >= $pokokPajak;

        $this->selectedTaxId = $tax->id;
        $this->searchResults = [];

        $this->taxData = [
            'id' => $tax->id,
            'billing_code' => $tax->billing_code,
            'jenis_pajak' => $tax->jenisPajak->nama ?? '-',
            'sub_jenis_pajak' => $tax->subJenisPajak->nama ?? '-',
            'nama_objek' => $tax->taxObject->nama_objek_pajak ?? '-',
            'alamat_objek' => $tax->taxObject->alamat_objek ?? '-',
            'nopd' => $tax->taxObject->nopd ?? '-',
            'masa_pajak' => $tax->masa_pajak_bulan
                ? Carbon::create()->month((int) $tax->masa_pajak_bulan)->translatedFormat('F') . ' ' . $tax->masa_pajak_tahun
                : ($tax->masa_pajak_tahun ?? '-'),
            'masa_pajak_bulan' => $tax->masa_pajak_bulan,
            'masa_pajak_tahun' => $tax->masa_pajak_tahun,
            'pokok_pajak' => $pokokPajak,
            'sanksi_existing' => $sanksiExisting,
            'total_principal_paid' => $totalPrincipalPaid,
            'total_penalty_paid' => $totalPenaltyPaid,
            'pokok_belum_dibayar' => $pokokBelumDibayar,
            'sanksi_belum_dibayar' => $sanksiBelumDibayar,
            'is_pokok_lunas' => $isPokokLunas,
            'status' => $tax->display_status->value,
            'status_label' => $tax->display_status_label,
            'jatuh_tempo' => $tax->payment_expired_at
                ? Carbon::parse($tax->payment_expired_at)->format('Y-m-d')
                : null,
            'is_opd' => $tax->isOpd(),
            'is_insidentil' => $tax->isInsidentil(),
        ];

        $this->wpData = $wp ? [
            'nama' => $wp->nama_lengkap,
            'npwpd' => $wp->npwpd,
            'nik' => $wp->nik,
            'alamat' => $wp->alamat ?? '-',
        ] : null;

        // Auto-determine tipe berdasarkan kondisi pembayaran
        if ($isPokokLunas && $sanksiBelumDibayar > 0) {
            // Pokok lunas, sanksi belum → sanksi_saja
            $this->tipeStpd = 'sanksi_saja';
            $this->hitungSanksiSaja();
        } elseif (!$isPokokLunas) {
            // Pokok belum lunas → pokok_sanksi
            $this->tipeStpd = 'pokok_sanksi';
        }

        // OPD / insidentil warning
        if ($tax->isOpd() || $tax->isInsidentil()) {
            Notification::make()
                ->warning()
                ->title('Perhatian')
                ->body('Billing ini berstatus ' . ($tax->isOpd() ? 'OPD' : 'Insidentil') . ' — sanksi otomatis Rp 0.')
                ->send();
        }
    }

    // ── Change Tipe ───────────────────────────────────────────────────────

    public function updatedTipeStpd(): void
    {
        $this->resetHitungan();

        if ($this->tipeStpd === 'sanksi_saja') {
            $this->hitungSanksiSaja();
        }
    }

    public function updatedProyeksiTanggalBayar(): void
    {
        if ($this->tipeStpd === 'pokok_sanksi' && $this->proyeksiTanggalBayar) {
            $this->hitungProyeksi();
        }
    }

    // ── Perhitungan ───────────────────────────────────────────────────────

    private function hitungSanksiSaja(): void
    {
        if (!$this->taxData) return;

        $sanksiBelum = $this->taxData['sanksi_belum_dibayar'];
        $this->hitungSanksi = $sanksiBelum;
        $this->hitungPokokBelumDibayar = 0;

        // Hitung bulan terlambat sampai sekarang
        $masaPajak = Carbon::create($this->taxData['masa_pajak_tahun'], $this->taxData['masa_pajak_bulan'] ?? 1, 1);
        $jatuhTempo = $this->taxData['jatuh_tempo']
            ? Carbon::parse($this->taxData['jatuh_tempo'])
            : Tax::hitungJatuhTempoSelfAssessment(
                (int) ($this->taxData['masa_pajak_bulan'] ?? 1),
                (int) $this->taxData['masa_pajak_tahun']
            );
        $this->hitungBulanTerlambat = Tax::hitungBulanTerlambat($jatuhTempo, Carbon::now());
        $this->tarifSanksiPersen = Tax::getTarifSanksi($masaPajak) * 100;
    }

    public function hitungProyeksi(): void
    {
        if (!$this->taxData || !$this->proyeksiTanggalBayar) return;

        $pokokPajak = $this->taxData['pokok_pajak'];
        $masaPajak = Carbon::create($this->taxData['masa_pajak_tahun'], $this->taxData['masa_pajak_bulan'] ?? 1, 1);
        $jatuhTempo = $this->taxData['jatuh_tempo']
            ? Carbon::parse($this->taxData['jatuh_tempo'])
            : Tax::hitungJatuhTempoSelfAssessment(
                (int) ($this->taxData['masa_pajak_bulan'] ?? 1),
                (int) $this->taxData['masa_pajak_tahun']
            );
        $tanggalBayar = Carbon::parse($this->proyeksiTanggalBayar);

        $isOpd = $this->taxData['is_opd'] ?? false;
        $isInsidentil = $this->taxData['is_insidentil'] ?? false;

        $result = Tax::hitungSanksi($pokokPajak, $masaPajak, $jatuhTempo, $tanggalBayar, $isOpd, $isInsidentil);

        $this->hitungBulanTerlambat = $result['bulan_terlambat'];
        $this->hitungSanksi = $result['denda'];
        $this->hitungPokokBelumDibayar = $this->taxData['pokok_belum_dibayar'];
        $this->tarifSanksiPersen = $result['tarif_sanksi'] * 100;
    }

    // ── Submit ────────────────────────────────────────────────────────────

    public function buatStpd(): void
    {
        if (!$this->selectedTaxId || !$this->taxData) {
            Notification::make()->warning()->title('Pilih billing terlebih dahulu.')->send();
            return;
        }

        if (!$this->tipeStpd) {
            Notification::make()->warning()->title('Pilih tipe STPD.')->send();
            return;
        }

        if ($this->tipeStpd === 'pokok_sanksi' && !$this->proyeksiTanggalBayar) {
            Notification::make()->warning()->title('Masukkan proyeksi tanggal bayar.')->send();
            return;
        }

        if ($this->hitungSanksi === null || $this->hitungSanksi <= 0) {
            if (!($this->taxData['is_opd'] ?? false) && !($this->taxData['is_insidentil'] ?? false)) {
                Notification::make()->warning()->title('Sanksi harus lebih dari 0.')->send();
                return;
            }
        }

        try {
            $stpd = StpdManual::create([
                'tax_id' => $this->selectedTaxId,
                'tipe' => $this->tipeStpd,
                'status' => 'draft',
                'proyeksi_tanggal_bayar' => $this->tipeStpd === 'pokok_sanksi'
                    ? $this->proyeksiTanggalBayar
                    : null,
                'bulan_terlambat' => $this->hitungBulanTerlambat ?? 0,
                'sanksi_dihitung' => $this->hitungSanksi ?? 0,
                'pokok_belum_dibayar' => $this->hitungPokokBelumDibayar ?? 0,
                'catatan_petugas' => $this->catatanPetugas,
                'petugas_id' => auth()->id(),
                'petugas_nama' => auth()->user()->nama_lengkap ?? auth()->user()->name,
                'tanggal_buat' => now(),
            ]);

            $tipeLabel = $this->tipeStpd === 'pokok_sanksi'
                ? 'Pokok & Sanksi'
                : 'Sanksi Saja';

            $this->stpdResult = [
                'tipe' => $tipeLabel,
                'billing_code' => $this->taxData['billing_code'],
                'nama_wp' => $this->wpData['nama'] ?? '-',
                'sanksi' => $this->hitungSanksi,
                'verifikasi_url' => StpdManualResource::getUrl('index'),
            ];

            Notification::make()
                ->success()
                ->title('Draft STPD Berhasil Dibuat')
                ->body("Tipe: {$tipeLabel}. Menunggu verifikasi.")
                ->send();

            NotificationService::notifyRole(
                'verifikator',
                'Draft STPD Menunggu Verifikasi',
                "Draft STPD ({$tipeLabel}) untuk Billing Sumber {$this->taxData['billing_code']} telah dibuat oleh petugas dan menunggu verifikasi."
            );
        } catch (Exception $e) {
            Notification::make()
                ->danger()
                ->title('Gagal membuat STPD')
                ->body($e->getMessage())
                ->send();
        }
    }

    // ── Reset ─────────────────────────────────────────────────────────────

    public function buatBaru(): void
    {
        $this->resetSelection();
        $this->searchKeyword = null;
        $this->searchResults = [];
        $this->stpdResult = null;
    }

    private function resetSelection(): void
    {
        $this->selectedTaxId = null;
        $this->taxData = null;
        $this->wpData = null;
        $this->tipeStpd = null;
        $this->proyeksiTanggalBayar = null;
        $this->catatanPetugas = null;
        $this->stpdResult = null;
        $this->resetHitungan();
    }

    private function resetHitungan(): void
    {
        $this->hitungBulanTerlambat = null;
        $this->hitungSanksi = null;
        $this->hitungPokokBelumDibayar = null;
        $this->tarifSanksiPersen = null;
        $this->proyeksiTanggalBayar = null;
    }
}
