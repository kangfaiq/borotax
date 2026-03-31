<?php

namespace App\Policies;

use App\Domain\AirTanah\Models\MeterReport;
use App\Domain\Auth\Models\User;

class MeterReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'verifikator', 'petugas']);
    }

    public function view(User $user, MeterReport $meterReport): bool
    {
        return $user->hasRole(['admin', 'verifikator', 'petugas']);
    }

    public function create(User $user): bool
    {
        return false; // Created by System via Mobile
    }

    public function update(User $user, MeterReport $meterReport): bool
    {
        // Petugas memproses laporan
        return $user->hasRole(['admin', 'petugas']);
    }

    public function delete(User $user, MeterReport $meterReport): bool
    {
        return $user->isAdmin();
    }
}
