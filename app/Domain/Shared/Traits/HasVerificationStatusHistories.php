<?php

namespace App\Domain\Shared\Traits;

use App\Domain\Shared\Models\VerificationStatusHistory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasVerificationStatusHistories
{
    public function verificationStatusHistories(): MorphMany
    {
        return $this->morphMany(VerificationStatusHistory::class, 'subject')
            ->orderByDesc('happened_at')
            ->orderByDesc('created_at');
    }
}