<?php

namespace App\Filament\Pages;

use App\Domain\Shared\Traits\CalculatesJatuhTempo;
use Exception;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxPayment;
use App\Domain\Auth\Models\User;
use App\Enums\TaxStatus;
use App\Domain\WajibPajak\Models\WajibPajak;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use Carbon\Carbon;

class LunasBayarManual extends Page
{
    use WithFileUploads;
    protected static string | \BackedEnum | null $navigationIcon  = 'heroicon-o-check-badge';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Petugas';
    protected static ?string $navigationLabel = 'Lunas Bayar Manual';
    protected static ?string $title           = 'Lunas Bayar Manual';
    protected static ?int    $navigationSort  = 5;
    protected string  $view            = 'filament.pages.lunas-bayar-manual';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    // --- State: Search ---
    public ?string $searchBillingCode = null;

    // --- State: Found Record ---
    public ?Tax $foundTax = null;
    public ?array $taxDetails = null;
    public $foundTaxesList = [];

    // --- State: Form Pembayaran ---
    public ?float $inputPokokPajak = null;
    public ?float $inputSanksiPajak = null;
    public ?string $inputTanggalBayar = null;
    public ?string $inputLokasiPembayaran = null;
    public ?string $inputReferensiBayar = null;
    public $inputBuktiBayar = null; // File upload

    // --- State: Success ---
    public ?array $successData = null;

    // --- State: Confirmation Popup ---
    public bool $isConfirmingPayment = false;
    public string $paymentConfirmationType = 'pas';

    public function mount(): void
    {
        $this->inputTanggalBayar = Carbon::now()->format('Y-m-d\TH:i');
    }

    public function searchBilling(): void
    {
        Tax::syncExpiredStatuses();

        $code = trim($this->searchBillingCode ?? '');

        if (empty($code)) {
            Notification::make()->warning()->title('Masukkan Kode Pembayaran Aktif atau NPWPD terlebih dahulu.')->send();
            return;
        }

        $this->resetStates();

        if (strlen($code) === 18) {
            // Search by Billing Code
            $tax = Tax::with(['taxObject', 'jenisPajak', 'subJenisPajak', 'user'])
                ->wherePaymentCode($code)
                ->first();

            if (!$tax) {
                Notification::make()->danger()->title('Billing tidak ditemukan.')->send();
                return;
            }

            if (!$tax->canBePaidManually()) {
                $this->resetStates();
                Notification::make()
                    ->warning()
                    ->title('Billing tidak valid')
                    ->body("Kode Pembayaran Aktif {$code} memiliki status: " . strtoupper($tax->display_status->value) . ". Hanya billing Pending, Terverifikasi (SKPD), Kedaluwarsa, Dibayar Sebagian, atau billing Lunas dengan sanksi yang masih tersisa yang dapat dilunaskan manual.")
                    ->send();
                return;
            }

            $this->selectTaxToPay($tax->id);

        } elseif (strlen($code) === 13) {
            // Search by NPWPD
            $wp = WajibPajak::where('npwpd', $code)->first();
            if (!$wp) {
                Notification::make()->danger()->title('NPWPD tidak ditemukan.')->send();
                return;
            }

            $taxes = Tax::with(['taxObject', 'jenisPajak', 'subJenisPajak'])
                ->where('user_id', $wp->user_id)
                ->whereIn('status', [TaxStatus::Pending, TaxStatus::Verified, TaxStatus::Expired, TaxStatus::PartiallyPaid, TaxStatus::Paid])
                ->get()
                ->filter(fn (Tax $tax) => $tax->canBePaidManually())
                ->values();

            if ($taxes->isEmpty()) {
                Notification::make()->info()->title('Tidak ada tagihan aktif.')->body('Tidak ada tagihan yang menunggu pembayaran untuk NPWPD tersebut.')->send();
                return;
            }

            if ($taxes->count() === 1) {
                $this->selectTaxToPay($taxes->first()->id);
            } else {
                $this->foundTaxesList = $taxes->map(function ($t) use ($wp) {
                    return [
                        'id' => $t->id,
                        'billing_code' => $t->getPreferredPaymentCode(),
                        'source_billing_code' => $t->billing_code,
                        'stpd_payment_code' => $t->stpd_payment_code,
                        'has_stpd_alias' => !empty($t->stpd_payment_code),
                        'nama_objek' => $t->taxObject->nama_objek_pajak ?? '-',
                        'masa_pajak' => Carbon::create()->month((int) $t->masa_pajak_bulan)->translatedFormat('F') . ' ' . $t->masa_pajak_tahun,
                        'total_tagihan' => (float) $t->amount + (float) $t->sanksi,
                        'sisa_tagihan' => $t->getRemainingAmount(),
                        'jatuh_tempo' => $t->payment_expired_at ? Carbon::parse($t->payment_expired_at)->format('d/m/Y') : '-',
                        'status' => $t->display_status->value,
                        'status_label' => $t->display_status_label,
                    ];
                })->toArray();
            }
        } else {
            Notification::make()->warning()->title('Format pencarian tidak valid')->body('Silakan masukkan 18 digit Kode Pembayaran Aktif atau 13 digit NPWPD.')->send();
        }
    }

    public function selectTaxToPay(string $taxId): void
    {
        $tax = Tax::with(['taxObject', 'jenisPajak', 'subJenisPajak', 'user'])->find($taxId);
        if (!$tax) return;

        $this->foundTax = $tax;
        $this->foundTaxesList = [];
        
        // Prepare display details
        $wp = WajibPajak::where('user_id', $tax->user_id)->first();
        
        $this->taxDetails = [
            'id' => $tax->id,
            'billing_code' => $tax->getPreferredPaymentCode(),
            'source_billing_code' => $tax->billing_code,
            'stpd_payment_code' => $tax->stpd_payment_code,
            'has_stpd_alias' => !empty($tax->stpd_payment_code),
            'nama_wp' => $wp ? $wp->nama_lengkap : ($tax->user->nama_lengkap ?? $tax->user->name),
            'npwpd' => $wp ? $wp->npwpd : '-',
            'nama_objek' => $tax->taxObject->nama_objek_pajak ?? '-',
            'alamat_objek' => $tax->taxObject->alamat_objek ?? '-',
            'jenis_pajak' => $tax->jenisPajak->nama ?? '-',
            'masa_pajak' => Carbon::create()->month((int) $tax->masa_pajak_bulan)->translatedFormat('F') . ' ' . $tax->masa_pajak_tahun,
            'jatuh_tempo' => $tax->payment_expired_at ? Carbon::parse($tax->payment_expired_at)->format('d/m/Y') : '-',
            'pokok_asli' => (float) $tax->amount,
            'sanksi_asli' => (float) $tax->sanksi,
            'total_tagihan' => (float) $tax->amount + (float) $tax->sanksi,
            'total_dibayar' => $tax->getTotalPaid(),
            'sisa_tagihan' => $tax->getRemainingAmount(),
            'status' => $tax->display_status->value,
            'status_label' => $tax->display_status_label,
        ];

        // Pre-fill form
        $this->inputPokokPajak = min((float) $tax->amount, $tax->getRemainingAmount());
        $this->inputSanksiPajak = $tax->getRemainingAmount() > (float) $tax->amount ? min((float) $tax->sanksi, $tax->getRemainingAmount() - (float) $tax->amount) : 0;
        $this->inputTanggalBayar = Carbon::now()->format('Y-m-d\TH:i');
        $this->inputLokasiPembayaran = 'MANUAL';
        $this->inputReferensiBayar = null;
        $this->inputBuktiBayar = null;
        $this->successData = null;

        $this->recalculateSanksiRecommendation();
    }

    public function updatedInputTanggalBayar(): void
    {
        $this->recalculateSanksiRecommendation();
    }

    public function recalculateSanksiRecommendation(): void
    {
        if (!$this->foundTax || !$this->inputTanggalBayar || !isset($this->taxDetails)) return;

        $tax = $this->foundTax;
        
        $this->taxDetails['sanksi_rekomen'] = 0;
        $this->taxDetails['bulan_terlambat'] = 0;

        if ($tax->payment_expired_at) {
            $jatuhTempo = Carbon::parse($tax->payment_expired_at);
            $tanggalBayar = Carbon::parse($this->inputTanggalBayar);
            
            if ($tanggalBayar->startOfDay()->isAfter($jatuhTempo->startOfDay()) && $tax->amount > 0) {
                $masaPajak = Carbon::createFromFormat('Y-m', $tax->masa_pajak_tahun . '-' . str_pad($tax->masa_pajak_bulan, 2, '0', STR_PAD_LEFT))->startOfMonth();
                
                $sanksiInfo = CalculatesJatuhTempo::hitungSanksi(
                    pokokPajak: (float) $tax->amount,
                    masaPajak: $masaPajak,
                    jatuhTempo: $jatuhTempo,
                    tanggalBayar: $tanggalBayar,
                    isOpd: $tax->isOpd(),
                    isInsidentil: $tax->isInsidentil()
                );
                
                $this->taxDetails['sanksi_rekomen'] = $sanksiInfo['denda'];
                $this->taxDetails['bulan_terlambat'] = $sanksiInfo['bulan_terlambat'];

                // Update input sanksi suggestively if the input is untouched or 0
                if ($this->inputSanksiPajak == 0) {
                    $this->inputSanksiPajak = $sanksiInfo['denda'];
                }
            }
        }
    }

    public function lunaskanBilling(): void
    {
        if (!$this->foundTax) {
            Notification::make()->danger()->title('Data billing tidak valid.')->send();
            return;
        }

        // Validasi input
        if ($this->inputPokokPajak === null || $this->inputPokokPajak < 0) {
            Notification::make()->warning()->title('Pokok Pajak tidak valid.')->send();
            return;
        }

        if ($this->inputSanksiPajak === null || $this->inputSanksiPajak < 0) {
            Notification::make()->warning()->title('Sanksi Pajak tidak valid.')->send();
            return;
        }

        if (empty($this->inputTanggalBayar)) {
            Notification::make()->warning()->title('Tanggal bayar harus diisi.')->send();
            return;
        }
        
        if (empty($this->inputLokasiPembayaran)) {
            Notification::make()->warning()->title('Lokasi pembayaran harus dipilih.')->send();
            return;
        }

        if (empty(trim($this->inputReferensiBayar ?? ''))) {
            Notification::make()->warning()->title('Referensi bukti bayar harus diisi.')->send();
            return;
        }

        if (!$this->inputBuktiBayar) {
            Notification::make()->warning()->title('Bukti pembayaran fisik wajib diunggah.')->send();
            return;
        }

        $tax = Tax::find($this->foundTax->id);
        $pokok = (float) $this->inputPokokPajak;
        $sanksi = (float) $this->inputSanksiPajak;
        $totalBayarMasuk = $pokok + $sanksi;
        $sisaTagihan = $tax->getRemainingAmount();

        // Check if under or overpaid for confirmation
        if ($totalBayarMasuk > $sisaTagihan) {
            $this->paymentConfirmationType = 'lebih';
            $this->isConfirmingPayment = true;
            return;
        } elseif ($totalBayarMasuk < $sisaTagihan) {
            $this->paymentConfirmationType = 'kurang';
            $this->isConfirmingPayment = true;
            return;
        }

        // Exact amount -> execute directly
        $this->executeLunaskanBilling();
    }

    public function cancelPaymentConfirmation(): void
    {
        $this->isConfirmingPayment = false;
        $this->paymentConfirmationType = 'pas';
    }

    public function executeLunaskanBilling(): void
    {
        try {
            DB::beginTransaction();

            $tax = Tax::find($this->foundTax->id);
            $pokok = (float) $this->inputPokokPajak;
            $sanksi = (float) $this->inputSanksiPajak;
            $totalBayarMasuk = $pokok + $sanksi;
            $tglBayar = Carbon::parse($this->inputTanggalBayar);
            $adminName = auth()->user()->nama_lengkap ?? auth()->user()->name;

            $this->isConfirmingPayment = false;

            // Upload File
            $attachmentPath = null;
            if ($this->inputBuktiBayar) {
                // Ensure directory exists
                $directory = 'tax_payments_attachments/' . date('Y/m');
                $attachmentPath = $this->inputBuktiBayar->store($directory, 'public');
            }

            // 1. Create TaxPayment record
            TaxPayment::create([
                'tax_id' => $tax->id,
                'external_ref' => 'MANUAL-' . time(),
                'amount_paid' => $totalBayarMasuk,
                'principal_paid' => $pokok,
                'penalty_paid' => $sanksi,
                'payment_channel' => $this->inputLokasiPembayaran,
                'paid_at' => $tglBayar,
                'description' => "Pembayaran manual oleh admin: {$adminName}. Ref: " . trim($this->inputReferensiBayar),
                'attachment_url' => $attachmentPath,
                'raw_response' => ['note' => 'Manual payment via Admin Panel'],
            ]);

            // 2. Check total paid and update Tax record
            $akumulasiPembayaran = $tax->getTotalPaid(); // Uses fresh relation data
            $totalTagihanFinal = (float) $tax->amount + (float) $tax->sanksi;

            if ($akumulasiPembayaran >= $totalTagihanFinal) {
                // Lunas Penuh
                $tax->update([
                    'status' => TaxStatus::Paid,
                    'paid_at' => $tglBayar,
                    'payment_channel' => $this->inputLokasiPembayaran,
                    'payment_ref' => trim($this->inputReferensiBayar)
                ]);
                $statusPesan = 'Lunas';
            } else {
                // Lunas Sebagian (Cicilan)
                $tax->update([
                    'status' => TaxStatus::PartiallyPaid,
                    // Note: do not set paid_at for partial, wait until fully paid
                ]);
                $statusPesan = 'Dibayar Sebagian (Cicilan)';
            }
            
            // TaxObserver will auto-handle sptpd_number and stpd_number based on status change.
            // For partially_paid where status didn't change (e.g. cicilan kedua), re-check STPD issuance.
            $tax->fresh()->checkAndIssueStpd();

            DB::commit();

            // Prepare success data
            $this->successData = [
                'tax_id' => $tax->id,
                'billing_code' => $tax->getPreferredPaymentCode(),
                'source_billing_code' => $tax->billing_code,
                'stpd_payment_code' => $tax->stpd_payment_code,
                'has_stpd_alias' => !empty($tax->stpd_payment_code),
                'nama_wp' => $this->taxDetails['nama_wp'],
                'masa_pajak' => $this->taxDetails['masa_pajak'],
                'total_bayar' => $totalBayarMasuk,
                'sisa_tagihan' => $tax->fresh()->getRemainingAmount(),
                'tgl_bayar' => $tglBayar->translatedFormat('d F Y H:i'),
                'referensi' => trim($this->inputReferensiBayar),
                'keterangan_status' => $statusPesan,
                'is_lunas' => $statusPesan === 'Lunas',
            ];

            // Send Notification to WP User
            $wpUser = User::find($tax->user_id);
            if ($wpUser) {
                Notification::make()
                    ->title('Pembayaran Diterima')
                    ->body("Pembayaran " . ($statusPesan === 'Lunas' ? 'lunas' : 'sebagian') . " untuk Kode Pembayaran Aktif {$tax->getPreferredPaymentCode()} sebesar Rp " . number_format($totalBayarMasuk, 0, ',', '.') . " telah diterima pada " . $tglBayar->format('d/m/Y') . ".")
                    ->sendToDatabase($wpUser);
            }

            // Show UI success
            Notification::make()->success()->title("Billing Berhasil $statusPesan")->send();
            
            // Reset state
            $this->foundTax = null;
            $this->taxDetails = null;

        } catch (Exception $e) {
            DB::rollBack();
            Notification::make()->danger()->title('Gagal memproses pembayaran')->body($e->getMessage())->send();
        }
    }

    public function resetForm(): void
    {
        $this->searchBillingCode = null;
        $this->resetStates();
        $this->successData = null;
    }

    private function resetStates(): void
    {
        $this->foundTax = null;
        $this->taxDetails = null;
        $this->foundTaxesList = [];
        $this->inputPokokPajak = null;
        $this->inputSanksiPajak = null;
        $this->inputTanggalBayar = Carbon::now()->format('Y-m-d\TH:i');
        $this->inputLokasiPembayaran = 'MANUAL';
        $this->inputReferensiBayar = null;
        $this->inputBuktiBayar = null;
    }
}
