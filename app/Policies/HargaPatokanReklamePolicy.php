<?php

namespace App\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Reklame\Models\HargaPatokanReklame;

class HargaPatokanReklamePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, HargaPatokanReklame $hargaPatokanReklame): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, HargaPatokanReklame $hargaPatokanReklame): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, HargaPatokanReklame $hargaPatokanReklame): bool
    {
        return $user->isAdmin();
    }
}