<?php

namespace App\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Models\DataChangeRequest;

class DataChangeRequestPolicy
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
    public function view(User $user, DataChangeRequest $request): bool
    {
        return $user->hasRole(['admin', 'verifikator']) || $request->requested_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'petugas']);
    }

    /**
     * Determine whether the user can review (approve/reject).
     */
    public function review(User $user, DataChangeRequest $request): bool
    {
        return $user->hasRole(['admin', 'verifikator'])
            && $request->isPending()
            && $request->requested_by !== $user->id; // Tidak bisa review sendiri
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DataChangeRequest $request): bool
    {
        return $user->hasRole('admin');
    }
}
