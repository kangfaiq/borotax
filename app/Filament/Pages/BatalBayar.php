<?php

namespace App\Filament\Pages;

use Exception;
use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxPayment;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Enums\TaxStatus;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BatalBayar extends Page
{
    protected static string | \BackedEnum | null $navigationIcon  = 'heroicon-o-arrow-path';
    protected static string | \UnitEnum | null $navigationGroup = 'Laporan Petugas';
    protected static ?string $navigationLabel = 'Batal Bayar';
    protected static ?string $title           = 'Pembatalan Pembayaran';
    protected static ?int    $navigationSort  = 6;
    protected string  $view            = 'filament.pages.batal-bayar';

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
    public $payments = []; // Collection of TaxPayment arrays

    // --- State: Cancel Dialog ---
    public ?string $cancelPaymentId = null;
    public ?string $cancelReason = null;
    public bool $isConfirmingCancel = false;

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
            $tax = Tax::with(['taxObject', 'jenisPajak', 'user', 'payments' => function($q) {
                $q->orderBy('paid_at', 'desc');
            }])
                ->wherePaymentCode($code)
                ->first();

            if (!$tax) {
                Notification::make()->danger()->title('Billing tidak ditemukan.')->send();
                return;
            }

            if ($tax->payments->isEmpty()) {
                Notification::make()
                    ->warning()
                    ->title('Tidak Ada Pembayaran')
                    ->body("Kode Pembayaran Aktif {$code} saat ini berstatus " . strtoupper($tax->status) . " dan belum memiliki riwayat pembayaran yang bisa dibatalkan.")
                    ->send();
                return;
            }

            $this->selectTaxToCancel($tax->id);

        } elseif (strlen($code) === 13) {
            // Search by NPWPD
            $wp = WajibPajak::where('npwpd', $code)->first();
            if (!$wp) {
                Notification::make()->danger()->title('NPWPD tidak ditemukan.')->send();
                return;
            }

            $taxes = Tax::with(['taxObject', 'jenisPajak', 'payments'])
                ->where('user_id', $wp->user_id)
                ->whereHas('payments')
                ->get();

            if ($taxes->isEmpty()) {
                Notification::make()->info()->title('Tidak ada tagihan dengan pembayaran.')->body('Tidak ada riwayat pembayaran yang bisa dibatalkan untuk NPWPD ini.')->send();
                return;
            }

            if ($taxes->count() === 1) {
                $this->selectTaxToCancel($taxes->first()->id);
            } else {
                $this->foundTaxesList = $taxes->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'billing_code' => $t->getPreferredPaymentCode(),
                        'source_billing_code' => $t->billing_code,
                        'stpd_payment_code' => $t->stpd_payment_code,
                        'has_stpd_alias' => !empty($t->stpd_payment_code),
                        'nama_objek' => $t->taxObject->nama_objek_pajak ?? '-',
                        'masa_pajak' => Carbon::create()->month((int) $t->masa_pajak_bulan)->translatedFormat('F') . ' ' . $t->masa_pajak_tahun,
                        'total_tagihan' => (float) $t->amount + (float) $t->sanksi,
                        'total_dibayar' => $t->payments->sum('amount_paid'),
                        'status' => $t->display_status->value,
                        'status_label' => $t->display_status_label,
                    ];
                })->toArray();
            }
        } else {
            Notification::make()->warning()->title('Format pencarian tidak valid')->body('Silakan masukkan 18 digit Kode Pembayaran Aktif atau 13 digit NPWPD.')->send();
        }
    }

    public function selectTaxToCancel(string $taxId): void
    {
        $tax = Tax::with(['taxObject', 'jenisPajak', 'user', 'payments' => function($q) {
            $q->orderBy('paid_at', 'desc');
        }])->find($taxId);

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
            'jenis_pajak' => $tax->jenisPajak->nama ?? '-',
            'masa_pajak' => Carbon::create()->month((int) $tax->masa_pajak_bulan)->translatedFormat('F') . ' ' . $tax->masa_pajak_tahun,
            'total_tagihan' => (float) $tax->amount + (float) $tax->sanksi,
            'total_dibayar' => $tax->getTotalPaid(),
            'status' => $tax->display_status->value,
            'status_label' => $tax->display_status_label,
        ];

        // Format payments specifically for blade iteration
        $this->refreshPaymentsList();
        
        $this->cancelPaymentId = null;
        $this->cancelReason = null;
        $this->isConfirmingCancel = false;
    }

    private function refreshPaymentsList(): void
    {
        if (!$this->foundTax) return;
        
        $this->foundTax->load(['payments' => function($q) {
            $q->orderBy('paid_at', 'desc');
        }]);

        $this->payments = $this->foundTax->payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'paid_at' => $payment->paid_at->format('d/m/Y H:i'),
                'amount_paid' => (float) $payment->amount_paid,
                'principal_paid' => (float) $payment->principal_paid,
                'penalty_paid' => (float) $payment->penalty_paid,
                'payment_channel' => $payment->payment_channel,
                'description' => $payment->description,
            ];
        })->toArray();
    }

    public function confirmCancelPayment(string $paymentId): void
    {
        $this->cancelPaymentId = $paymentId;
        $this->cancelReason = null;
        $this->isConfirmingCancel = true;
    }

    public function cancelConfirm(): void
    {
        $this->cancelPaymentId = null;
        $this->cancelReason = null;
        $this->isConfirmingCancel = false;
    }

    public function executeCancelPayment(): void
    {
        if (!$this->isConfirmingCancel || !$this->cancelPaymentId) {
            return;
        }

        if (empty(trim($this->cancelReason))) {
            Notification::make()->warning()->title('Alasan pembatalan wajib diisi.')->send();
            return;
        }

        try {
            DB::beginTransaction();

            $paymentToCancel = TaxPayment::findOrFail($this->cancelPaymentId);
            $tax = Tax::findOrFail($this->foundTax->id);

            // 1. Soft delete and meta
            $paymentToCancel->update([
                'cancelled_reason' => trim($this->cancelReason),
                'cancelled_by' => auth()->user()->id,
            ]);
            $paymentToCancel->delete(); // applies soft deletes

            // 2. Recalculate Tax Status
            $newTotalPaid = $tax->getTotalPaid(); // fresh sum excluding soft-deleted
            $totalTagihan = (float) $tax->amount + (float) $tax->sanksi;
            $rollbackStatus = $tax->resolveStatusAfterPaymentRollback();

            if ($newTotalPaid <= 0) {
                $tax->update([
                    'status' => $rollbackStatus,
                    'paid_at' => null,
                    'payment_channel' => null,
                    'payment_ref' => null,
                    'sptpd_number' => null,
                    'stpd_number' => null,
                    'stpd_payment_code' => null,
                ]);
                $newStatus = $rollbackStatus->value . ' (' . ($rollbackStatus->getLabel() ?? $rollbackStatus->value) . ')';
            } elseif ($newTotalPaid < $totalTagihan) {
                $documentState = $tax->resolveDocumentStateAfterPaymentRollback();

                $tax->update([
                    'status' => $rollbackStatus,
                    'paid_at' => null,
                    'sptpd_number' => $documentState['sptpd_number'],
                    'stpd_number' => $documentState['stpd_number'],
                    'stpd_payment_code' => $documentState['stpd_payment_code'],
                ]);
                $newStatus = $rollbackStatus->value . ' (' . ($rollbackStatus->getLabel() ?? $rollbackStatus->value) . ')';
            } else {
                $tax->update(['status' => TaxStatus::Paid]);
                $newStatus = 'paid (Lunas)';
            }

            DB::commit();

            Notification::make()->success()
                ->title('Pembayaran Dibatalkan')
                ->body('Status tagihan diperbarui menjadi: ' . $newStatus)
                ->send();

            // Refresh UI state
            $this->taxDetails['total_dibayar'] = $tax->getTotalPaid();
            $this->taxDetails['status'] = $tax->fresh()->display_status->value;
            $this->taxDetails['status_label'] = $tax->fresh()->display_status_label;
            $this->refreshPaymentsList();
            
            // Close dialog
            $this->cancelConfirm();

            // If no payments left, maybe clear the whole view
            if (empty($this->payments)) {
                $this->resetStates();
            }

        } catch (Exception $e) {
            DB::rollBack();
            Notification::make()->danger()->title('Gagal membatalkan pembayaran')->body($e->getMessage())->send();
        }
    }

    private function resetStates(): void
    {
        $this->foundTax = null;
        $this->taxDetails = null;
        $this->foundTaxesList = [];
        $this->payments = [];
        $this->cancelPaymentId = null;
        $this->cancelReason = null;
        $this->isConfirmingCancel = false;
    }
}
