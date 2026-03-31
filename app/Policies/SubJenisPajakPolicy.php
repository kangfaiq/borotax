<?php

namespace App\Policies;

use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Auth\Models\User;
use Illuminate\Auth\Access\Response;

class SubJenisPajakPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SubJenisPajak $subJenisPajak): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SubJenisPajak $subJenisPajak): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SubJenisPajak $subJenisPajak): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SubJenisPajak $subJenisPajak): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SubJenisPajak $subJenisPajak): bool
    {
        return $user->isAdmin();
    }
}
