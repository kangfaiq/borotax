<?php

namespace App\Domain\Master\Models;

use App\Domain\Tax\Models\Tax;
use App\Domain\Tax\Models\TaxObject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPajak extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'jenis_pajak';

    protected $fillable = [
        'kode',
        'billing_kode_override',
        'nama',
        'nama_singkat',
        'deskripsi',
        'icon',
        'tarif_default',
        'tipe_assessment',
        'is_active',
        'urutan',
        'opsen_persen',
    ];

    protected $casts = [
        'tarif_default' => 'decimal:2',
        'is_active' => 'boolean',
        'urutan' => 'integer',
        'opsen_persen' => 'decimal:2',
    ];

    /**
     * Get sub jenis pajak
     */
    public function subJenisPajak(): HasMany
    {
        return $this->hasMany(SubJenisPajak::class, 'jenis_pajak_id');
    }

    /**
     * Get tax objects
     */
    public function taxObjects(): HasMany
    {
        return $this->hasMany(TaxObject::class, 'jenis_pajak_id');
    }

    /**
     * Get taxes
     */
    public function taxes(): HasMany
    {
        return $this->hasMany(Tax::class, 'jenis_pajak_id');
    }

    /**
     * Scope untuk jenis pajak aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk urutan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }

    public function isPbjt(): bool
    {
        return in_array($this->kode, ['41101', '41102', '41103', '41107'], true);
    }

    public function getBillingKode(): string
    {
        return $this->billing_kode_override ?? $this->kode;
    }
}
