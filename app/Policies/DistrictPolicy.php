<?php

namespace App\Policies;

use App\Domain\Region\Models\District;
use App\Domain\Auth\Models\User;

class DistrictPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, District $district): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, District $district): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, District $district): bool
    {
        return $user->isAdmin();
    }
}
