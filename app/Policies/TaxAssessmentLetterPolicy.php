<?php

namespace App\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Tax\Models\TaxAssessmentLetter;

class TaxAssessmentLetterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'verifikator', 'petugas']);
    }

    public function view(User $user, TaxAssessmentLetter $letter): bool
    {
        return $user->hasRole(['admin', 'verifikator', 'petugas']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'petugas']);
    }

    public function update(User $user, TaxAssessmentLetter $letter): bool
    {
        return $user->hasRole(['admin', 'petugas']) && $letter->isDraft();
    }

    public function delete(User $user, TaxAssessmentLetter $letter): bool
    {
        return $user->hasRole('admin') && $letter->isDraft();
    }

    public function review(User $user, TaxAssessmentLetter $letter): bool
    {
        return $user->hasRole(['admin', 'verifikator'])
            && $letter->isDraft()
            && $letter->created_by !== $user->id;
    }

    public function allocate(User $user, TaxAssessmentLetter $letter): bool
    {
        return $user->hasRole(['admin', 'petugas']) && $letter->isApproved() && $letter->allowsCompensation();
    }
}