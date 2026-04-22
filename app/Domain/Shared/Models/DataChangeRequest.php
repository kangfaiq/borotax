<?php

namespace App\Domain\Shared\Models;

use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Tax\Models\TaxObject;
use InvalidArgumentException;
use Throwable;
use App\Domain\Auth\Models\User;
use App\Domain\Shared\Services\NotificationService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class DataChangeRequest extends Model
{
    use SoftDeletes, HasFactory, HasUuids;

    protected $table = 'data_change_requests';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'field_changes',
        'alasan_perubahan',
        'dokumen_pendukung',
        'status',
        'catatan_review',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // ─── Encrypted JSON accessor untuk field_changes ─────────────

    public function setFieldChangesAttribute(array $value): void
    {
        $this->attributes['field_changes'] = Crypt::encryptString(json_encode($value));
    }

    public function getFieldChangesAttribute(?string $value): ?array
    {
        if ($value === null) return null;
        try {
            return json_decode(Crypt::decryptString($value), true);
        } catch (Throwable $e) {
            return json_decode($value, true);
        }
    }

    // ─── Relations ───────────────────────────────────────────────

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get entity model (polymorphic manual karena entity_type = nama tabel)
     */
    public function getEntityModel(): ?Model
    {
        $modelClass = $this->resolveModelClass();
        if (!$modelClass) return null;

        return $modelClass::find($this->entity_id);
    }

    /**
     * Resolve class model dari entity_type (nama tabel)
     */
    private function resolveModelClass(): ?string
    {
        return match ($this->entity_type) {
            'wajib_pajak' => WajibPajak::class,
            'tax_objects' => TaxObject::class,
            default => null,
        };
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForEntity($query, string $entityType, string $entityId)
    {
        return $query->where('entity_type', $entityType)
            ->where('entity_id', $entityId);
    }

    // ─── Actions ─────────────────────────────────────────────────

    /**
     * Setujui permintaan perubahan — apply perubahan ke entity dan log ke ActivityLog
     */
    public function approve(?string $catatanReview = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        return DB::transaction(function () use ($catatanReview) {
            $entity = $this->getEntityModel();
            if (!$entity) return false;

            $fieldChanges = $this->field_changes;

            // Simpan old values sebelum update (untuk encrypted fields, ambil dari field_changes)
            $oldValues = [];
            $newValues = [];
            foreach ($fieldChanges as $field => $change) {
                $oldValues[$field] = $change['old'];
                $newValues[$field] = $change['new'];
            }

            // Apply perubahan ke entity
            foreach ($newValues as $field => $value) {
                $entity->{$field} = $value;
            }
            $entity->save();

            // Log ke ActivityLog
            ActivityLog::log(
                action: 'APPROVE_DATA_CHANGE',
                targetTable: $this->entity_type,
                targetId: $this->entity_id,
                description: "Menyetujui perubahan data. Alasan: {$this->alasan_perubahan}",
                oldValues: $oldValues,
                newValues: $newValues,
            );

            // Update status permintaan
            $this->update([
                'status' => 'approved',
                'catatan_review' => $catatanReview,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return true;
        });
    }

    /**
     * Tolak permintaan perubahan
     */
    public function reject(string $catatanReview): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'rejected',
            'catatan_review' => $catatanReview,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        ActivityLog::log(
            action: 'REJECT_DATA_CHANGE',
            targetTable: $this->entity_type,
            targetId: $this->entity_id,
            description: "Menolak perubahan data. Alasan penolakan: {$catatanReview}",
        );

        return true;
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Buat permintaan perubahan baru
     */
    public static function createRequest(
        Model $entity,
        array $fieldChanges,
        string $alasanPerubahan,
        ?string $dokumenPendukung = null,
    ): self {
        // Build field_changes format: {field: {old: ..., new: ...}}
        $changes = [];
        foreach ($fieldChanges as $field => $newValue) {
            $changes[$field] = [
                'old' => $entity->getAttribute($field),
                'new' => $newValue,
            ];
        }

        // Filter: hanya yang benar-benar berubah
        $changes = array_filter($changes, function ($change) {
            return (string) ($change['old'] ?? '') !== (string) ($change['new'] ?? '');
        });

        if (empty($changes)) {
            throw new InvalidArgumentException('Tidak ada field yang berubah.');
        }

        $request = self::create([
            'entity_type' => $entity->getTable(),
            'entity_id' => $entity->getKey(),
            'field_changes' => $changes,
            'alasan_perubahan' => $alasanPerubahan,
            'dokumen_pendukung' => $dokumenPendukung,
            'status' => 'pending',
            'requested_by' => auth()->id(),
        ]);

        ActivityLog::log(
            action: 'REQUEST_DATA_CHANGE',
            targetTable: $entity->getTable(),
            targetId: $entity->getKey(),
            description: "Mengajukan perubahan data. Alasan: {$alasanPerubahan}. Field: " . implode(', ', array_keys($changes)),
        );

        $entityLabel = $request->getEntityTypeLabel();
        $requesterName = optional($request->requester)->nama_lengkap
            ?? optional($request->requester)->name
            ?? 'Petugas';

        NotificationService::notifyRole(
            roles: ['admin', 'verifikator'],
            title: "Permintaan Perubahan Data {$entityLabel}",
            body: "{$requesterName} mengajukan perubahan {$entityLabel}. Alasan: {$alasanPerubahan}. Field: " . implode(', ', array_keys($changes)),
            data: [
                'data_change_request_id' => $request->id,
                'entity_type' => $entity->getTable(),
                'entity_id' => $entity->getKey(),
            ],
        );

        return $request;
    }

    /**
     * Label entity type untuk tampilan
     */
    public function getEntityTypeLabel(): string
    {
        return match ($this->entity_type) {
            'wajib_pajak' => 'Wajib Pajak',
            'tax_objects' => 'Objek Pajak',
            default => $this->entity_type,
        };
    }

    /**
     * Label status untuk tampilan
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Review',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => $this->status,
        };
    }

    /**
     * Cek apakah masih bisa di-review
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Cek apakah ada pending request untuk entity yang sama
     */
    public static function hasPendingFor(Model $entity): bool
    {
        return self::where('entity_type', $entity->getTable())
            ->where('entity_id', $entity->getKey())
            ->where('status', 'pending')
            ->exists();
    }
}
