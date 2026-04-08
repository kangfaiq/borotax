<?php

namespace App\Policies;

use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\Auth\Models\User;

class ReklameRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'petugas']);
    }

    public function view(User $user, ReklameRequest $reklameRequest): bool
    {
        return $user->hasRole(['admin', 'petugas']);
    }

    public function create(User $user): bool
    {
        return false; // Created by System via Mobile API
    }

    public function update(User $user, ReklameRequest $reklameRequest): bool
    {
        // Petugas memproses pengajuan
        return $user->hasRole(['admin', 'petugas']);
    }

    public function delete(User $user, ReklameRequest $reklameRequest): bool
    {
        return $user->isAdmin();
    }
}
