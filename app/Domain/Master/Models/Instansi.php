<?php

namespace App\Domain\Master\Models;

use App\Domain\Tax\Models\PortalMblbSubmission;
use App\Domain\Tax\Models\Tax;
use App\Enums\InstansiKategori;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Instansi extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'instansi';

    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'alamat',
        'keterangan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'kategori' => InstansiKategori::class,
            'is_active' => 'boolean',
        ];
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(Tax::class, 'instansi_id');
    }

    public function portalMblbSubmissions(): HasMany
    {
        return $this->hasMany(PortalMblbSubmission::class, 'instansi_id');
    }

    public function toTransactionAttributes(): array
    {
        return [
            'instansi_id' => $this->id,
            'instansi_nama' => $this->nama,
            'instansi_kategori' => $this->kategori?->value,
        ];
    }
}