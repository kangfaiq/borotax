<?php

namespace App\Policies;

use App\Domain\Tax\Models\HargaPatokanSarangWalet;
use App\Domain\Auth\Models\User;

class HargaPatokanSarangWaletPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, HargaPatokanSarangWalet $hargaPatokanSarangWalet): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, HargaPatokanSarangWalet $hargaPatokanSarangWalet): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, HargaPatokanSarangWalet $hargaPatokanSarangWalet): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, HargaPatokanSarangWalet $hargaPatokanSarangWalet): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, HargaPatokanSarangWalet $hargaPatokanSarangWalet): bool
    {
        return $user->isAdmin();
    }
}
