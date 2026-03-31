<?php

namespace App\Policies;

use App\Domain\Region\Models\Village;
use App\Domain\Auth\Models\User;

class VillagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Village $village): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Village $village): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Village $village): bool
    {
        return $user->isAdmin();
    }
}
