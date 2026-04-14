<?php

namespace App\Domain\Shared\Models;

use Exception;
use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    use HasFactory, HasUuids;

    public const ACTION_SYNC_EXPIRED_TAX_STATUSES = 'SYNC_EXPIRED_TAX_STATUSES';

    public const ACTION_UPDATE_REKLAME_MATERIAL_FILE = 'UPDATE_REKLAME_MATERIAL_FILE';

    protected const ACTION_LABELS = [
        'UPDATE_TAX_OBJECT_FROM_SKPD_REKLAME_APPROVAL' => 'Sinkronisasi Objek dari Persetujuan SKPD Reklame',
        self::ACTION_SYNC_EXPIRED_TAX_STATUSES => 'Sinkronisasi Billing Kedaluwarsa',
        self::ACTION_UPDATE_REKLAME_MATERIAL_FILE => 'Perubahan File Materi Reklame',
    ];

    protected $table = 'activity_logs';

    protected $fillable = [
        'actor_id',
        'actor_type',
        'action',
        'summary_count',
        'source_statuses',
        'target_table',
        'target_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    /**
     * Encrypt old_values / new_values saat set, decrypt saat get
     */
    public function setOldValuesAttribute(?array $value): void
    {
        $this->attributes['old_values'] = $value !== null
            ? Crypt::encryptString(json_encode($value))
            : null;
    }

    public function getOldValuesAttribute(?string $value): ?array
    {
        if ($value === null) return null;
        try {
            return json_decode(Crypt::decryptString($value), true);
        } catch (Exception $e) {
            return json_decode($value, true); // fallback jika tidak terenkripsi
        }
    }

    public function setNewValuesAttribute(?array $value): void
    {
        $this->attributes['new_values'] = $value !== null
            ? Crypt::encryptString(json_encode($value))
            : null;
    }

    public function getNewValuesAttribute(?string $value): ?array
    {
        if ($value === null) return null;
        try {
            return json_decode(Crypt::decryptString($value), true);
        } catch (Exception $e) {
            return json_decode($value, true);
        }
    }

    /**
     * Get actor
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getActionLabelAttribute(): string
    {
        return static::ACTION_LABELS[$this->action]
            ?? Str::of((string) $this->action)
                ->replace('_', ' ')
                ->lower()
                ->headline()
                ->toString();
    }

    public function isExpiredTaxSync(): bool
    {
        return $this->action === self::ACTION_SYNC_EXPIRED_TAX_STATUSES;
    }

    public function getAutoExpireCountAttribute(): ?int
    {
        if (! $this->isExpiredTaxSync()) {
            return null;
        }

        return (int) data_get($this->new_values, 'count');
    }

    public function getAutoExpireSourceStatusSummaryAttribute(): ?string
    {
        if (! $this->isExpiredTaxSync()) {
            return null;
        }

        $breakdown = collect(data_get($this->new_values, 'source_status_breakdown', []));

        if ($breakdown->isEmpty()) {
            return null;
        }

        return $breakdown
            ->map(fn (array $item): string => sprintf('%s: %d billing', $item['label'], $item['count']))
            ->implode('; ');
    }

    public function hasSourceStatus(string $status): bool
    {
        return str_contains((string) $this->source_statuses, ',' . $status . ',');
    }

    public function getAutoExpireBillingSummaryAttribute(): ?string
    {
        if (! $this->isExpiredTaxSync()) {
            return null;
        }

        $billingCodes = collect(data_get($this->new_values, 'billing_codes', []));

        if ($billingCodes->isEmpty()) {
            return null;
        }

        $visibleCodes = $billingCodes->take(5);
        $remainingCount = $billingCodes->count() - $visibleCodes->count();

        return $visibleCodes->implode(', ')
            . ($remainingCount > 0 ? " (+{$remainingCount} lainnya)" : '');
    }

    public function getAutoExpireJenisPajakSummaryAttribute(): ?string
    {
        if (! $this->isExpiredTaxSync()) {
            return null;
        }

        $breakdown = collect(data_get($this->new_values, 'jenis_pajak_breakdown', []));

        if ($breakdown->isEmpty()) {
            return null;
        }

        return $breakdown
            ->map(fn (array $item): string => sprintf('%s: %d billing', $item['label'], $item['count']))
            ->implode('; ');
    }

    /**
     * Scope untuk actor tertentu
     */
    public function scopeByActor($query, string $actorId)
    {
        return $query->where('actor_id', $actorId);
    }

    /**
     * Scope untuk action tertentu
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope untuk target tertentu
     */
    public function scopeForTarget($query, string $targetTable, string $targetId)
    {
        return $query->where('target_table', $targetTable)
            ->where('target_id', $targetId);
    }

    /**
     * Create log entry (backward-compatible)
     */
    public static function log(
        string $action,
        ?string $actorId = null,
        string $actorType = 'user',
        ?string $targetTable = null,
        ?string $targetId = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $summaryCount = null,
        ?string $sourceStatuses = null,
    ): self {
        return self::create([
            'actor_id' => $actorId ?? auth()->id(),
            'actor_type' => $actorType,
            'action' => $action,
            'summary_count' => $summaryCount,
            'source_statuses' => $sourceStatuses,
            'target_table' => $targetTable,
            'target_id' => $targetId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log perubahan field pada model.
     * Otomatis mendeteksi field yang berubah (dirty).
     *
     * @param Model $model Model yang berubah
     * @param string $action Action label (e.g. UPDATE_WAJIB_PAJAK)
     * @param string|null $description Deskripsi tambahan
     * @param array|null $oldValues Override old values (untuk encrypted fields)
     * @return self|null Null jika tidak ada perubahan
     */
    public static function logChanges(
        Model $model,
        string $action,
        ?string $description = null,
        ?array $oldValues = null,
    ): ?self {
        // Jika oldValues tidak diberikan, detect dari dirty attributes
        if ($oldValues === null) {
            $dirty = $model->getDirty();
            if (empty($dirty)) return null;

            $oldValues = [];
            foreach (array_keys($dirty) as $key) {
                $oldValues[$key] = $model->getOriginal($key);
            }
            $newValues = $dirty;
        } else {
            // oldValues diberikan manual (untuk encrypted fields), compute newValues dari model saat ini
            $newValues = [];
            foreach (array_keys($oldValues) as $key) {
                $newValues[$key] = $model->getAttribute($key);
            }
        }

        // Filter: hanya yang benar-benar berubah
        $changedOld = [];
        $changedNew = [];
        foreach ($oldValues as $key => $oldVal) {
            $newVal = $newValues[$key] ?? null;
            if ((string) $oldVal !== (string) $newVal) {
                $changedOld[$key] = $oldVal;
                $changedNew[$key] = $newVal;
            }
        }

        if (empty($changedOld)) return null;

        return self::log(
            action: $action,
            targetTable: $model->getTable(),
            targetId: $model->getKey(),
            description: $description,
            oldValues: $changedOld,
            newValues: $changedNew,
        );
    }
}
