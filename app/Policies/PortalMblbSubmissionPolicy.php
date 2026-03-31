<?php

namespace App\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Tax\Models\PortalMblbSubmission;

class PortalMblbSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function view(User $user, PortalMblbSubmission $submission): bool
    {
        return $user->hasRole(['admin', 'verifikator']);
    }

    public function review(User $user, PortalMblbSubmission $submission): bool
    {
        return $user->hasRole(['admin', 'verifikator']) && $submission->isPending();
    }

    public function delete(User $user, PortalMblbSubmission $submission): bool
    {
        return $user->hasRole('admin');
    }
}