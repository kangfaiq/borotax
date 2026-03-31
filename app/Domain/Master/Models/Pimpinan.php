<?php

namespace App\Domain\Master\Models;

use App\Domain\Auth\Models\User;
use App\Domain\AirTanah\Models\SkpdAirTanah;
use App\Domain\Reklame\Models\SkpdReklame;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pimpinan extends Model
{
    use HasUuids;

    protected $table = 'pimpinan';

    protected $fillable = [
        'kab',
        'opd',
        'jabatan',
        'bidang',
        'sub_bidang',
        'nama',
        'pangkat',
        'nip',
    ];

    /**
     * Get verifikator users assigned to this pimpinan
     */
    public function verifikators(): HasMany
    {
        return $this->hasMany(User::class, 'pimpinan_id');
    }

    /**
     * Get SKPD Reklame yang ditandatangani pimpinan ini
     */
    public function skpdReklame(): HasMany
    {
        return $this->hasMany(SkpdReklame::class, 'pimpinan_id');
    }

    /**
     * Get SKPD Air Tanah yang ditandatangani pimpinan ini
     */
    public function skpdAirTanah(): HasMany
    {
        return $this->hasMany(SkpdAirTanah::class, 'pimpinan_id');
    }
}
