<?php

namespace App\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Instansi;

class InstansiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Instansi $instansi): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Instansi $instansi): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Instansi $instansi): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Instansi $instansi): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Instansi $instansi): bool
    {
        return $user->isAdmin();
    }
}