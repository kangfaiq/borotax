<?php

namespace App\Domain\Tax\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Services\VerificationStatusHistoryService;
use App\Domain\Shared\Traits\HasVerificationStatusHistories;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembetulanRequest extends Model
{
    use SoftDeletes, HasUuids, HasVerificationStatusHistories;
    protected $table = 'pembetulan_requests';

    protected $fillable = [
        'tax_id',
        'user_id',
        'alasan',
        'omzet_baru',
        'lampiran',
        'status',
        'catatan_petugas',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'omzet_baru' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (self $request): void {
            $request->recordInitialVerificationHistory();
        });

        static::updated(function (self $request): void {
            if (! $request->wasChanged('status')) {
                return;
            }

            $request->recordStatusTransitionVerificationHistory();
        });
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    private function recordInitialVerificationHistory(): void
    {
        app(VerificationStatusHistoryService::class)->record(
            $this,
            null,
            $this->status,
            'submitted',
            $this->user,
            $this->alasan,
        );
    }

    private function recordStatusTransitionVerificationHistory(): void
    {
        app(VerificationStatusHistoryService::class)->record(
            $this,
            $this->getOriginal('status'),
            $this->status,
            match ($this->status) {
                'diproses' => 'processing_started',
                'selesai' => 'completed',
                'ditolak' => 'rejected',
                default => 'status_updated',
            },
            $this->resolveVerificationHistoryActor(),
            $this->catatan_petugas,
        );
    }

    private function resolveVerificationHistoryActor(): ?User
    {
        $actor = $this->processor ?? auth()->user();

        return $actor instanceof User ? $actor : null;
    }
}
