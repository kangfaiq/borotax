<?php

namespace App\Policies;

use App\Domain\Tax\Models\Tax;
use App\Domain\Auth\Models\User;

class TaxPolicy
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
    public function view(User $user, Tax $tax): bool
    {
        return $user->hasRole(['admin', 'verifikator', 'petugas']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Transaksi pajak dibuat via sistem / API biasanya, atau Petugas input manual
        // Jika Petugas boleh input manual (seperti di loket), maka:
        return $user->hasRole(['admin', 'petugas']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tax $tax): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tax $tax): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tax $tax): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tax $tax): bool
    {
        return $user->isAdmin();
    }
}
