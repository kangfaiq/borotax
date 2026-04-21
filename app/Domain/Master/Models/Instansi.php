<?php

namespace App\Domain\Master\Models;

use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\Village;
use App\Domain\Tax\Models\PortalMblbSubmission;
use App\Domain\Tax\Models\Tax;
use App\Enums\InstansiKategori;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'asal_wilayah',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
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

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'regency_code', 'code');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_code', 'code');
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