<?php

namespace App\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Tax\Models\PembetulanRequest;

class PembetulanRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'petugas']);
    }

    public function view(User $user, PembetulanRequest $pembetulanRequest): bool
    {
        return $user->hasRole(['admin', 'petugas']);
    }

    public function review(User $user, PembetulanRequest $pembetulanRequest): bool
    {
        return $user->hasRole(['admin', 'petugas']);
    }

    public function delete(User $user, PembetulanRequest $pembetulanRequest): bool
    {
        return $user->hasRole('admin');
    }
}