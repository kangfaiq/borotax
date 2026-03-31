<?php

namespace App\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Tax\Models\HargaSatuanListrik;

class HargaSatuanListrikPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, HargaSatuanListrik $hargaSatuanListrik): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, HargaSatuanListrik $hargaSatuanListrik): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, HargaSatuanListrik $hargaSatuanListrik): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, HargaSatuanListrik $hargaSatuanListrik): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, HargaSatuanListrik $hargaSatuanListrik): bool
    {
        return $user->isAdmin();
    }
}