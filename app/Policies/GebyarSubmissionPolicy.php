<?php

namespace App\Policies;

use App\Domain\Gebyar\Models\GebyarSubmission;
use App\Domain\Auth\Models\User;

class GebyarSubmissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GebyarSubmission $submission): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // Submission only from Mobile App
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GebyarSubmission $submission): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GebyarSubmission $submission): bool
    {
        return $user->isAdmin();
    }
}
