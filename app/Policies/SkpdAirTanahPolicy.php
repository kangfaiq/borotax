<?php

namespace App\Policies;

use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\Auth\Models\User;

class SkpdAirTanahPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function view(User $user, SkpdAirTanah $skpdAirTanah): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function create(User $user): bool
    {
        // Created by System when Petugas process report
        return $user->isAdmin();
    }

    public function update(User $user, SkpdAirTanah $skpdAirTanah): bool
    {
        // Verifikator approve SKPD
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function delete(User $user, SkpdAirTanah $skpdAirTanah): bool
    {
        return $user->isAdmin();
    }

    public function verify(User $user): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }
}
