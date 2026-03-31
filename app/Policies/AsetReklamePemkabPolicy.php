<?php

namespace App\Policies;

use App\Domain\Reklame\Models\AsetReklamePemkab;
use App\Domain\Auth\Models\User;

class AsetReklamePemkabPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'verifikator', 'petugas']);
    }

    public function view(User $user, AsetReklamePemkab $aset): bool
    {
        return $user->hasRole(['admin', 'verifikator', 'petugas']);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, AsetReklamePemkab $aset): bool
    {
        return $user->hasRole(['admin', 'petugas']);
    }

    public function delete(User $user, AsetReklamePemkab $aset): bool
    {
        return $user->isAdmin();
    }
}
