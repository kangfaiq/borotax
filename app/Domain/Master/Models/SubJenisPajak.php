<?php

namespace App\Domain\Master\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubJenisPajak extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'sub_jenis_pajak';

    protected $fillable = [
        'jenis_pajak_id',
        'kode',
        'nama',
        'nama_lengkap',
        'deskripsi',
        'icon',
        'tarif_persen',
        'is_insidentil',
        'is_active',
        'urutan',
        'berlaku_mulai',
        'berlaku_sampai',
        'dasar_hukum',
    ];

    protected $casts = [
        'tarif_persen' => 'decimal:2',
        'is_insidentil' => 'boolean',
        'is_active' => 'boolean',
        'urutan' => 'integer',
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
    ];

    /**
     * Get jenis pajak induk
     */
    public function jenisPajak(): BelongsTo
    {
        return $this->belongsTo(JenisPajak::class, 'jenis_pajak_id');
    }

    /**
     * Get tax objects
     */
    public function taxObjects(): HasMany
    {
        return $this->hasMany(TaxObject::class, 'sub_jenis_pajak_id');
    }

    /**
     * Scope untuk sub jenis pajak aktif
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

    /**
     * Get nama lengkap dengan jenis pajak
     */
    public function getNamaLengkapAttribute(): string
    {
        return $this->attributes['nama_lengkap'] ?? ($this->jenisPajak->nama_singkat . ' - ' . $this->nama);
    }
}
