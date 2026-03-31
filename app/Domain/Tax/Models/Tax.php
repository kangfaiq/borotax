<?php

namespace App\Domain\Tax\Models;

use App\Domain\Shared\Traits\HasEncryptedAttributes;
use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Tax\Models\TaxAssessmentLetter;
use App\Domain\Tax\Observers\TaxObserver;
use App\Enums\TaxStatus;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Domain\Shared\Traits\CalculatesJatuhTempo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy([TaxObserver::class])]
class Tax extends Model
{
    use HasFactory, HasUuids, HasEncryptedAttributes, CalculatesJatuhTempo, SoftDeletes;

    protected $table = 'taxes';

    /**
     * Kolom yang dienkripsi
     */
    protected array $encryptedAttributes = [
        'amount',
        'omzet',
        'sanksi',
        'opsen',
        'attachment_url',
        'meter_photo_url',
        'latitude',
        'longitude',
    ];

    protected $fillable = [
        'jenis_pajak_id',
        'sub_jenis_pajak_id',
        'tax_object_id',
        'user_id',
        'amount',
        'omzet',
        'sanksi',
        'tarif_persentase',
        'status',
        'billing_code',
        'skpd_number',
        'notes',
        'attachment_url',
        'paid_at',
        'verified_at',
        'verified_by',
        'meter_reading',
        'previous_meter_reading',
        'meter_photo_url',
        'latitude',
        'longitude',
        'rejection_reason',
        'payment_channel',
        'payment_ref',
        'payment_fee',
        'payment_expired_at',
        'masa_pajak_bulan',
        'masa_pajak_tahun',
        'masa_pajak_tahun',
        'pembetulan_ke',
        'billing_sequence',
        'parent_tax_id',
        'sptpd_number',
        'stpd_number',
        'stpd_payment_code',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'opsen',
        'dasar_hukum',
        'is_legacy',
        'legacy_billing_code',
    ];

    protected $casts = [
        'status' => TaxStatus::class,
        'tarif_persentase' => 'decimal:2',
        'payment_fee' => 'decimal:2',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'payment_expired_at' => 'datetime',
        'meter_reading' => 'integer',
        'previous_meter_reading' => 'integer',
        'masa_pajak_bulan' => 'integer',
        'masa_pajak_tahun' => 'integer',
        'pembetulan_ke' => 'integer',
        'billing_sequence' => 'integer',
        'cancelled_at' => 'datetime',
        'is_legacy' => 'boolean',
    ];

    /**
     * Get jenis pajak
     */
    public function jenisPajak(): BelongsTo
    {
        return $this->belongsTo(JenisPajak::class, 'jenis_pajak_id');
    }

    /**
     * Get sub jenis pajak
     */
    public function subJenisPajak(): BelongsTo
    {
        return $this->belongsTo(SubJenisPajak::class, 'sub_jenis_pajak_id');
    }

    /**
     * Get tax object
     */
    public function taxObject(): BelongsTo
    {
        return $this->belongsTo(TaxObject::class, 'tax_object_id');
    }

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get verifikator
     */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get user who cancelled this tax
     */
    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Get parent tax (for pembetulan)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'parent_tax_id');
    }

    /**
     * Get children taxes (pembetulan revisions)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Tax::class, 'parent_tax_id');
    }

    /**
     * Get payments
     */
    public function payments(): HasMany
    {
        return $this->hasMany(TaxPayment::class);
    }

    public function stpdManuals(): HasMany
    {
        return $this->hasMany(StpdManual::class, 'tax_id');
    }

    public function taxAssessmentLetters(): HasMany
    {
        return $this->hasMany(TaxAssessmentLetter::class, 'source_tax_id');
    }

    /**
     * Get total amount paid from all payments
     *
     * @return float
     */
    public function getTotalPaid(): float
    {
        // 🔒 amount_paid is stored as an ENCRYPTED string in the database.
        // We cannot use SQL sum(). We must fetch them as models to decrypt them first,
        // then sum the float values using Laravel Collection.
        return (float) $this->payments()->get()->sum(function($payment) {
            return (float) $payment->amount_paid;
        });
    }

    /**
     * Get remaining amount to be paid (Pokok + Sanksi - Total Paid)
     *
     * @return float
     */
    public function getRemainingAmount(): float
    {
        $totalTagihan = (float) $this->amount + (float) $this->sanksi;
        $totalPaid = $this->getTotalPaid();
        
        return max(0, $totalTagihan - $totalPaid);
    }

    /**
     * Get total principal (pokok) paid from all payments.
     */
    public function getTotalPrincipalPaid(): float
    {
        return (float) $this->payments()->get()->sum(function($payment) {
            return (float) $payment->principal_paid;
        });
    }

    /**
     * Get total penalty (sanksi) paid from all payments.
     */
    public function getTotalPenaltyPaid(): float
    {
        return (float) $this->payments()->get()->sum(function($payment) {
            return (float) $payment->penalty_paid;
        });
    }

    /**
     * Get remaining unpaid sanksi amount.
     */
    public function getSanksiBelumDibayar(): float
    {
        $totalSanksi = (float) $this->sanksi;
        $totalPenaltyPaid = $this->getTotalPenaltyPaid();
        return max(0, $totalSanksi - $totalPenaltyPaid);
    }

    /**
     * Check if principal (pokok) is fully paid and the billing quarter is complete,
     * then auto-issue STPD.
     *
     * @return bool True if STPD was issued
     */
    public function checkAndIssueStpd(): bool
    {
        $sanksi = (float) $this->sanksi;
        if ($sanksi <= 0 || !empty($this->stpd_number)) {
            return false;
        }

        if ($this->isOpd() || $this->isInsidentil()) {
            return false;
        }

        $pokokPajak = (float) $this->amount;
        $totalPrincipalPaid = $this->getTotalPrincipalPaid();

        if ($totalPrincipalPaid >= $pokokPajak && $this->isTriwulanComplete()) {
            $this->stpd_number = $this->billing_code;
            $this->saveQuietly();
            return true;
        }

        return false;
    }

    public static function generateManualStpdPaymentCode(string $billingCode): string
    {
        if (!preg_match('/^\d{18}$/', $billingCode)) {
            throw new \InvalidArgumentException('Kode billing STPD manual harus berupa 18 digit angka.');
        }

        return substr($billingCode, 0, 7) . '77' . substr($billingCode, 9);
    }

    public function syncApprovedManualStpd(StpdManual $stpdManual): void
    {
        $this->sanksi = $stpdManual->sanksi_dihitung;
        $this->stpd_number = $stpdManual->nomor_stpd;
        $this->stpd_payment_code = $stpdManual->isTipeSanksi()
            ? static::generateManualStpdPaymentCode($this->billing_code)
            : null;

        $this->saveQuietly();
    }

    public function getPreferredPaymentCode(): string
    {
        return $this->stpd_payment_code ?: $this->billing_code;
    }

    public function canBePaidManually(): bool
    {
        return in_array($this->status, [TaxStatus::Pending, TaxStatus::Verified, TaxStatus::PartiallyPaid], true)
            || ($this->status === TaxStatus::Paid && $this->getRemainingAmount() > 0);
    }

    public function canCreateManualStpd(): bool
    {
        return in_array($this->status, [TaxStatus::Pending, TaxStatus::Verified, TaxStatus::PartiallyPaid], true)
            || ($this->status === TaxStatus::Paid && $this->getSanksiBelumDibayar() > 0);
    }

    public function mblbDetails(): HasMany
    {
        return $this->hasMany(TaxMblbDetail::class, 'tax_id');
    }

    public function sarangWaletDetail(): HasOne
    {
        return $this->hasOne(TaxSarangWaletDetail::class, 'tax_id');
    }

    public function ppjDetail(): HasOne
    {
        return $this->hasOne(TaxPpjDetail::class, 'tax_id');
    }

    /**
     * Check if this tax is MBLB (kode 41106)
     */
    public function isMblb(): bool
    {
        $jp = $this->jenisPajak ?? JenisPajak::find($this->jenis_pajak_id);
        return $jp && $jp->kode === '41106';
    }

    /**
     * Check if this tax is Sarang Burung Walet (kode 41109)
     */
    public function isSarangWalet(): bool
    {
        $jp = $this->jenisPajak ?? JenisPajak::find($this->jenis_pajak_id);
        return $jp && $jp->kode === '41109';
    }

    /**
     * Check if this tax is PPJ / PBJT atas Tenaga Listrik (kode 41105)
     */
    public function isPpj(): bool
    {
        $jp = $this->jenisPajak ?? JenisPajak::find($this->jenis_pajak_id);
        return $jp && $jp->kode === '41105';
    }

    /**
     * Check if this tax is PPJ Sumber Lain (PLN)
     */
    public function isPpjSumberLain(): bool
    {
        if (!$this->isPpj()) return false;
        $sjp = $this->subJenisPajak ?? SubJenisPajak::find($this->sub_jenis_pajak_id);
        return $sjp && $sjp->kode === 'PPJ_SUMBER_LAIN';
    }

    /**
     * Check if this tax is PPJ Dihasilkan Sendiri (Non PLN)
     */
    public function isPpjDihasilkanSendiri(): bool
    {
        if (!$this->isPpj()) return false;
        $sjp = $this->subJenisPajak ?? SubJenisPajak::find($this->sub_jenis_pajak_id);
        return $sjp && $sjp->kode === 'PPJ_DIHASILKAN_SENDIRI';
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeWherePaymentCode(Builder $query, string $code): Builder
    {
        return $query->where(function (Builder $builder) use ($code) {
            $builder->where('billing_code', $code)
                ->orWhere('stpd_payment_code', $code);
        });
    }

    /**
     * Scope untuk sudah bayar (hanya yang benar-benar terbayar)
     */
    public function scopePaid($query)
    {
        return $query->where('status', TaxStatus::Paid);
    }

    /**
     * Scope untuk pending
     */
    public function scopePending($query)
    {
        return $query->where('status', TaxStatus::Pending);
    }

    /**
     * Generate billing code sesuai format Pemda (18 karakter)
     *
     * Format: 35221 + XX + [000/00/0] + YY + [SSSS/SSSSS/SSSSSS] + XX
     *
     * Keterangan:
     *  - 35221       : Kode wilayah Bojonegoro (5 char)
     *  - XX          : 2 digit terakhir kode jenis pajak, misal '02' dari 41102 (2 char)
     *  - 000/00/0    : Padding tengah, menyesuaikan panjang nomor urut (3/2/1 char)
     *  - YY          : 2 digit terakhir tahun (2 char)
     *  - SSSS-SSSSSS : Nomor urut 4-6 digit (4/5/6 char)
     *  - XX          : 2 digit terakhir kode jenis pajak (2 char) — sama dengan posisi 6-7
     *
    * Contoh:
    *  - 352210200026000102 → Makanan & Minuman (41102), tahun 2026, urut ke-1
    *  - 352210100026000501 → Jasa Perhotelan (41101), tahun 2026, urut ke-5
    *  - 352210200261000002 → Makanan & Minuman (41102), tahun 2026, urut ke-10000
    *  - 352210200026000219 → Billing turunan surat ketetapan, tahun 2026, urut ke-2, suffix akhir khusus 19
     *
     * @param string $kodeJenisPajak Kode jenis pajak (misal '41102')
    * @param string|null $trailingSuffix Suffix 2 digit di posisi 17-18; default mengikuti 2 digit jenis pajak
     * @return string Kode billing 18 karakter
     */
    public static function generateBillingCode(string $kodeJenisPajak = '41102', ?string $trailingSuffix = null): string
    {
        $prefix = '35221';
        $kodeSuffix = substr($kodeJenisPajak, -2); // 2 digit terakhir, misal '02'
        $trailingSuffix ??= $kodeSuffix;
        $yearSuffix = date('y'); // misal '26'

        // Hitung nomor urut: jumlah billing yang sudah ada untuk jenis pajak + tahun ini
        $codePrefix = $prefix . $kodeSuffix;
        $count = static::withTrashed()->where('billing_code', 'like', $codePrefix . '%' . $trailingSuffix)
            ->whereYear('created_at', date('Y'))
            ->count();

        $sequence = $count + 1;

        // Format billing code
        $code = static::formatBillingCode($prefix, $kodeSuffix, $yearSuffix, $sequence, $trailingSuffix);

        // Pastikan unik (retry jika collision)
        while (static::withTrashed()->where('billing_code', $code)->exists()) {
            $sequence++;
            $code = static::formatBillingCode($prefix, $kodeSuffix, $yearSuffix, $sequence, $trailingSuffix);
        }

        return $code;
    }

    /**
     * Format billing code berdasarkan panjang nomor urut.
     * Total selalu 18 karakter, dengan suffix akhir yang dapat dioverride.
     */
    private static function formatBillingCode(string $prefix, string $kodeSuffix, string $yearSuffix, int $sequence, string $trailingSuffix): string
    {
        if ($sequence <= 9999) {
            // 35221 + XX + 000 + YY + SSSS + XX = 5+2+3+2+4+2 = 18
            $seqStr = str_pad($sequence, 4, '0', STR_PAD_LEFT);
            return $prefix . $kodeSuffix . '000' . $yearSuffix . $seqStr . $trailingSuffix;
        } elseif ($sequence <= 99999) {
            // 35221 + XX + 00 + YY + SSSSS + XX = 5+2+2+2+5+2 = 18
            $seqStr = str_pad($sequence, 5, '0', STR_PAD_LEFT);
            return $prefix . $kodeSuffix . '00' . $yearSuffix . $seqStr . $trailingSuffix;
        } else {
            // 35221 + XX + 0 + YY + SSSSSS + XX = 5+2+1+2+6+2 = 18
            $seqStr = str_pad($sequence, 6, '0', STR_PAD_LEFT);
            return $prefix . $kodeSuffix . '0' . $yearSuffix . $seqStr . $trailingSuffix;
        }
    }

    /**
     * Parse kode billing untuk mendapatkan informasi.
     *
     * @param string $billingCode Kode billing 18 karakter
    * @return array|null Array berisi prefix, kodeJenisPajak, suffix akhir, tahun, sequence, atau null jika invalid
     */
    public static function parseBillingCode(string $billingCode): ?array
    {
        if (strlen($billingCode) !== 18) {
            return null;
        }

        $prefix = substr($billingCode, 0, 5);
        if ($prefix !== '35221') {
            return null;
        }

        $kodeSuffix = substr($billingCode, 5, 2);
        $trailingSuffix = substr($billingCode, -2);

        // Middle section (8 chars antara kodeSuffix depan dan belakang)
        $middle = substr($billingCode, 7, 9); // posisi 8 s.d. 16

        // Deteksi format berdasarkan leading zeros di middle
        if (str_starts_with($middle, '000')) {
            // Standard: 000 + YY + SSSS
            $year = (int) substr($middle, 3, 2);
            $sequence = (int) substr($middle, 5, 4);
        } elseif (str_starts_with($middle, '00')) {
            // Extended: 00 + YY + SSSSS
            $year = (int) substr($middle, 2, 2);
            $sequence = (int) substr($middle, 4, 5);
        } else {
            // Super extended: 0 + YY + SSSSSS
            $year = (int) substr($middle, 1, 2);
            $sequence = (int) substr($middle, 3, 6);
        }

        $fullYear = $year + ($year >= 90 ? 1900 : 2000);

        return [
            'prefix' => $prefix,
            'kode_suffix' => $kodeSuffix,
            'trailing_suffix' => $trailingSuffix,
            'kode_jenis_pajak' => '411' . $kodeSuffix,
            'tahun' => $fullYear,
            'sequence' => $sequence,
        ];
    }

    /**
     * Check if expired
     */
    public function isExpired(): bool
    {
        return $this->payment_expired_at && $this->payment_expired_at->isPast();
    }

    /**
     * Check if paid
     */
    public function isPaid(): bool
    {
        return $this->status === TaxStatus::Paid;
    }

    /**
     * Check if this tax belongs to an OPD tax object (exempt from denda)
     */
    public function isOpd(): bool
    {
        return $this->taxObject && (bool) $this->taxObject->is_opd;
    }

    /**
     * Check if this tax belongs to an insidentil tax object
     */
    public function isInsidentil(): bool
    {
        return $this->taxObject && (bool) $this->taxObject->is_insidentil;
    }

    public function isPbjt(): bool
    {
        $jp = $this->jenisPajak ?? JenisPajak::find($this->jenis_pajak_id);

        return $jp?->isPbjt() ?? false;
    }

    /**
     * Check if this tax supports multi-billing per masa pajak (OPD or insidentil)
     */
    public function isMultiBilling(): bool
    {
        return $this->isOpd() || $this->isInsidentil();
    }

    /**
     * Check if this tax is a PBJT self-assessment type (Hotel, Restoran, Hiburan, Parkir).
     */
    public function isSelfAssessmentPbjt(): bool
    {
        return $this->isPbjt();
    }

    /**
     * Get the quarter start and end months for a given month.
     * Q1: Jan-Mar, Q2: Apr-Jun, Q3: Jul-Sep, Q4: Oct-Dec
     */
    public static function getTriwulanRange(int $bulan): array
    {
        if ($bulan <= 3) return [1, 2, 3];
        if ($bulan <= 6) return [4, 5, 6];
        if ($bulan <= 9) return [7, 8, 9];
        return [10, 11, 12];
    }

    /**
     * Cek apakah triwulan untuk billing ini sudah lengkap terbayar.
     *
     * Aturan PBJT (self_assessment):
     * - Objek reguler (bukan insidentil/OPD): harus lunas 1 triwulan penuh
     * - Objek baru yang mulai di tengah triwulan: cukup lunas sisa bulan triwulan
     * - Objek insidentil/OPD: langsung true (tidak perlu triwulan penuh)
     * - Non-PBJT (Reklame, Air Tanah): langsung true
     *
     * @return bool
     */
    public function isTriwulanComplete(): bool
    {
        // Non-PBJT → langsung true
        if (!$this->isSelfAssessmentPbjt()) {
            return true;
        }

        // Sarang Walet: masa pajak tahunan, langsung true
        if ($this->isSarangWalet()) {
            return true;
        }

        // Insidentil / OPD → langsung true
        if ($this->isMultiBilling()) {
            return true;
        }

        $tahun = (int) $this->masa_pajak_tahun;
        $bulan = (int) $this->masa_pajak_bulan;
        $triwulanMonths = self::getTriwulanRange($bulan);

        // Cari bulan awal billing pertama objek ini di triwulan yang sama
        // untuk menentukan apakah objek baru yang mulai di tengah triwulan
        $firstBillingInQuarter = Tax::where('tax_object_id', $this->tax_object_id)
            ->where('masa_pajak_tahun', $tahun)
            ->whereIn('masa_pajak_bulan', $triwulanMonths)
            ->where('pembetulan_ke', 0)
            ->where('billing_sequence', 0)
            ->orderBy('masa_pajak_bulan', 'asc')
            ->first();

        // Cari bulan awal objek secara keseluruhan (billing pertama ever)
        $firstBillingEver = Tax::where('tax_object_id', $this->tax_object_id)
            ->where('pembetulan_ke', 0)
            ->where('billing_sequence', 0)
            ->orderBy('masa_pajak_tahun', 'asc')
            ->orderBy('masa_pajak_bulan', 'asc')
            ->first();

        // Tentukan bulan-bulan yang harus terbayar
        $requiredMonths = $triwulanMonths;

        // Jika billing pertama objek ini ada di triwulan yang sama,
        // artinya objek baru → cukup mulai dari bulan pertama
        if ($firstBillingEver && $firstBillingInQuarter &&
            $firstBillingEver->id === $firstBillingInQuarter->id) {
            $startMonth = (int) $firstBillingInQuarter->masa_pajak_bulan;
            $requiredMonths = array_filter($triwulanMonths, fn($m) => $m >= $startMonth);
        }

        // Cek apakah semua bulan yang diperlukan sudah terbayar
        foreach ($requiredMonths as $month) {
            $paid = Tax::where('tax_object_id', $this->tax_object_id)
                ->where('masa_pajak_bulan', $month)
                ->where('masa_pajak_tahun', $tahun)
                ->where('pembetulan_ke', 0)
                ->where('billing_sequence', 0)
                ->whereIn('status', [TaxStatus::Paid])
                ->exists();

            if (!$paid) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get usage (untuk air tanah)
     */
    public function getUsageAttribute(): ?int
    {
        if ($this->meter_reading && $this->previous_meter_reading) {
            return $this->meter_reading - $this->previous_meter_reading;
        }
        return null;
    }
}
