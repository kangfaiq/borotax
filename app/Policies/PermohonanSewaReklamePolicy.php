<?php

namespace App\Policies;

use App\Domain\Reklame\Models\PermohonanSewaReklame;
use App\Domain\Auth\Models\User;

class PermohonanSewaReklamePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('petugas');
    }

    public function view(User $user, PermohonanSewaReklame $permohonan): bool
    {
        return $user->hasRole('petugas');
    }

    public function create(User $user): bool
    {
        // Created by authenticated users via portal or petugas
        return true;
    }

    public function update(User $user, PermohonanSewaReklame $permohonan): bool
    {
        return $user->hasRole(['admin', 'petugas']);
    }

    public function delete(User $user, PermohonanSewaReklame $permohonan): bool
    {
        return $user->isAdmin();
    }
}
