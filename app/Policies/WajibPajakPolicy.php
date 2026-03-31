<?php

namespace App\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\WajibPajak\Models\WajibPajak;

class WajibPajakPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'verifikator', 'petugas']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WajibPajak $wajibPajak): bool
    {
        return $user->hasRole(['admin', 'verifikator', 'petugas']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Petugas boleh input data WP baru di lapangan
        return $user->hasRole(['admin', 'petugas']);
    }

    /**
     * Determine whether the user can update the model.
     * Petugas bisa submit perubahan (akan masuk approval flow)
     */
    public function update(User $user, WajibPajak $wajibPajak): bool
    {
        return $user->hasRole(['admin', 'petugas']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WajibPajak $wajibPajak): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WajibPajak $wajibPajak): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WajibPajak $wajibPajak): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Custom Permission: Verify Wajib Pajak
     */
    public function verify(User $user): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }
}
