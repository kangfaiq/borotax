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

    protected const ACTION_LABELS = [
        'UPDATE_TAX_OBJECT_FROM_SKPD_REKLAME_APPROVAL' => 'Sinkronisasi Objek dari Persetujuan SKPD Reklame',
    ];

    protected $table = 'activity_logs';

    protected $fillable = [
        'actor_id',
        'actor_type',
        'action',
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
    ): self {
        return self::create([
            'actor_id' => $actorId ?? auth()->id(),
            'actor_type' => $actorType,
            'action' => $action,
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
