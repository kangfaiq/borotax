<?php

namespace App\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Reklame\Models\ReklameNilaiStrategis;

class ReklameNilaiStrategisPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, ReklameNilaiStrategis $reklameNilaiStrategis): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ReklameNilaiStrategis $reklameNilaiStrategis): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, ReklameNilaiStrategis $reklameNilaiStrategis): bool
    {
        return $user->isAdmin();
    }
}