<?php

namespace App\Domain\Shared\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\VerificationStatusHistory;
use Illuminate\Database\Eloquent\Model;

class VerificationStatusHistoryService
{
    public function record(
        Model $subject,
        ?string $fromStatus,
        string $toStatus,
        string $action,
        ?User $actor = null,
        ?string $note = null,
        ?array $metadata = null,
        bool $isOwnerVisible = true,
        $happenedAt = null,
    ): VerificationStatusHistory {
        $resolvedActor = $actor;

        if (! $resolvedActor && auth()->user() instanceof User) {
            $resolvedActor = auth()->user();
        }

        return $subject->verificationStatusHistories()->create([
            'actor_id' => $resolvedActor?->id,
            'actor_name' => $resolvedActor?->nama_lengkap ?? $resolvedActor?->name,
            'actor_role' => $resolvedActor?->role,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => filled($note) ? $note : null,
            'metadata' => $metadata,
            'is_owner_visible' => $isOwnerVisible,
            'happened_at' => $happenedAt ?? now(),
        ]);
    }
}