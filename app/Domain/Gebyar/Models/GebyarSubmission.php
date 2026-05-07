<?php

namespace App\Domain\Gebyar\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Shared\Services\VerificationStatusHistoryService;
use App\Domain\Shared\Traits\HasVerificationStatusHistories;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GebyarSubmission extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes, HasVerificationStatusHistories;
    protected $table = 'gebyar_submissions';

    /**
     * Kolom yang dienkripsi
     */
    protected array $encryptedAttributes = [
        'user_nik',
        'user_name',
        'place_name',
        'transaction_amount',
        'image_url',
        'original_image_url',
    ];

    protected $fillable = [
        'user_id',
        'user_nik',
        'user_name',
        'jenis_pajak_id',
        'place_name',
        'transaction_date',
        'transaction_amount',
        'transaction_amount_hash',
        'image_url',
        'original_image_url',
        'status',
        'period_year',
        'kupon_count',
        'rejection_reason',
        'verified_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'period_year' => 'integer',
        'kupon_count' => 'integer',
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (self $submission): void {
            $submission->recordInitialVerificationHistory();
        });

        static::updated(function (self $submission): void {
            if (! $submission->wasChanged('status')) {
                return;
            }

            $submission->recordStatusTransitionVerificationHistory();
        });
    }

    /**
     * Boot method untuk generate hash
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Generate hash untuk transaction_amount (untuk deteksi duplikat)
            if (isset($model->attributes['transaction_amount'])) {
                $model->transaction_amount_hash = User::generateHash($model->attributes['transaction_amount']);
            }
        });
    }

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get jenis pajak
     */
    public function jenisPajak(): BelongsTo
    {
        return $this->belongsTo(JenisPajak::class, 'jenis_pajak_id');
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk periode tertentu
     */
    public function scopePeriode($query, int $year)
    {
        return $query->where('period_year', $year);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => $this->status,
        };
    }

    private function recordInitialVerificationHistory(): void
    {
        app(VerificationStatusHistoryService::class)->record(
            $this,
            null,
            $this->status,
            'submitted',
            $this->user,
        );
    }

    private function recordStatusTransitionVerificationHistory(): void
    {
        $actor = auth()->user();

        app(VerificationStatusHistoryService::class)->record(
            $this,
            $this->getOriginal('status'),
            $this->status,
            match ($this->status) {
                'approved' => 'approved',
                'rejected' => 'rejected',
                default => 'status_updated',
            },
            $actor instanceof User ? $actor : null,
            $this->rejection_reason,
        );
    }
}
