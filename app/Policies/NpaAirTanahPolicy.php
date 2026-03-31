<?php

namespace App\Policies;

use App\Domain\AirTanah\Models\NpaAirTanah;
use App\Domain\Auth\Models\User;

class NpaAirTanahPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, NpaAirTanah $npaAirTanah): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, NpaAirTanah $npaAirTanah): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, NpaAirTanah $npaAirTanah): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, NpaAirTanah $npaAirTanah): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, NpaAirTanah $npaAirTanah): bool
    {
        return $user->isAdmin();
    }
}
