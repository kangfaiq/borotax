<?php

namespace App\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Retribusi\Models\SkrdSewaRetribusi;

class SkrdSewaRetribusiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function view(User $user, SkrdSewaRetribusi $skrd): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, SkrdSewaRetribusi $skrd): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function delete(User $user, SkrdSewaRetribusi $skrd): bool
    {
        return $user->isAdmin();
    }

    public function verify(User $user, SkrdSewaRetribusi $skrd): bool
    {
        return $user->hasRole(['admin', 'verifikator'])
            && $skrd->status === 'draft'
            && $skrd->petugas_id !== $user->id;
    }
}
