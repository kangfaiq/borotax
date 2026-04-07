<?php

namespace App\Policies;

use App\Domain\Reklame\Models\SkpdReklame;
use App\Domain\Auth\Models\User;

class SkpdReklamePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function view(User $user, SkpdReklame $skpdReklame): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function create(User $user): bool
    {
        // Created by System when Petugas process request
        return $user->isAdmin();
    }

    public function update(User $user, SkpdReklame $skpdReklame): bool
    {
        // Verifikator approve SKPD
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function delete(User $user, SkpdReklame $skpdReklame): bool
    {
        return $user->isAdmin();
    }

    public function verify(User $user, SkpdReklame $skpdReklame): bool
    {
        return $user->hasRole(['admin', 'verifikator'])
            && $skpdReklame->status === 'draft'
            && $skpdReklame->petugas_id !== $user->id;
    }
}
