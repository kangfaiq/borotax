<?php

namespace App\Policies;

use App\Domain\Tax\Models\StpdManual;
use App\Domain\Auth\Models\User;

class StpdManualPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function view(User $user, StpdManual $stpdManual): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, StpdManual $stpdManual): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function delete(User $user, StpdManual $stpdManual): bool
    {
        return $user->isAdmin();
    }

    public function verify(User $user, StpdManual $stpdManual): bool
    {
        return $user->hasRole(['admin', 'verifikator'])
            && $stpdManual->isDraft()
            && $stpdManual->petugas_id !== $user->id;
    }
}
