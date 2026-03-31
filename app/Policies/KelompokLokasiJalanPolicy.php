<?php

namespace App\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Reklame\Models\KelompokLokasiJalan;

class KelompokLokasiJalanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, KelompokLokasiJalan $kelompokLokasiJalan): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, KelompokLokasiJalan $kelompokLokasiJalan): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, KelompokLokasiJalan $kelompokLokasiJalan): bool
    {
        return $user->isAdmin();
    }
}