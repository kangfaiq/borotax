<?php

namespace App\Policies;

use App\Domain\Tax\Models\HargaPatokanMblb;
use App\Domain\Auth\Models\User;

class HargaPatokanMblbPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, HargaPatokanMblb $hargaPatokanMblb): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, HargaPatokanMblb $hargaPatokanMblb): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, HargaPatokanMblb $hargaPatokanMblb): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, HargaPatokanMblb $hargaPatokanMblb): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, HargaPatokanMblb $hargaPatokanMblb): bool
    {
        return $user->isAdmin();
    }
}
