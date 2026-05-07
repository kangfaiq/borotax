<?php

namespace App\Domain\Shared\Models;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VerificationStatusHistory extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'actor_id',
        'actor_name',
        'actor_role',
        'action',
        'from_status',
        'to_status',
        'note',
        'metadata',
        'is_owner_visible',
        'happened_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_owner_visible' => 'boolean',
        'happened_at' => 'datetime',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'submitted' => 'Pengajuan dibuat',
            'draft_created' => 'Draft dibuat',
            'processing_started' => 'Mulai diproses',
            'approved' => 'Pengajuan disetujui',
            'rejected' => 'Pengajuan ditolak',
            'completed' => 'Proses diselesaikan',
            'resubmitted' => 'Pengajuan dikirim ulang',
            'status_updated' => 'Status diperbarui',
            default => str($this->action)->replace('_', ' ')->headline()->toString(),
        };
    }

    public function getActorDisplayNameAttribute(): string
    {
        return $this->actor_name
            ?: $this->actor?->nama_lengkap
            ?: $this->actor?->name
            ?: 'Sistem';
    }

    public function getStatusTransitionLabelAttribute(): string
    {
        $toStatusLabel = self::labelForStatus($this->to_status);

        if (blank($this->from_status)) {
            return $toStatusLabel;
        }

        return self::labelForStatus($this->from_status) . ' -> ' . $toStatusLabel;
    }

    public static function labelForStatus(?string $status): string
    {
        return match ($status) {
            'pending', 'menungguVerifikasi' => 'Menunggu Verifikasi',
            'approved', 'disetujui' => 'Disetujui',
            'rejected', 'ditolak' => 'Ditolak',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
            'draft' => 'Draft',
            'diajukan' => 'Diajukan',
            null, '' => '-',
            default => str($status)->headline()->toString(),
        };
    }
}