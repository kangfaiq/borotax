<?php

namespace App\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Pimpinan;

class PimpinanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Pimpinan $pimpinan): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Pimpinan $pimpinan): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Pimpinan $pimpinan): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Pimpinan $pimpinan): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Pimpinan $pimpinan): bool
    {
        return $user->isAdmin();
    }
}