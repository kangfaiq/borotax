<?php

namespace App\Domain\Reklame\Models;

use App\Domain\Master\Models\SubJenisPajak;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HargaPatokanReklame extends Model
{
    use HasUuids;

    protected $table = 'harga_patokan_reklame';

    protected $fillable = [
        'sub_jenis_pajak_id',
        'kode',
        'nama',
        'nama_lengkap',
        'is_insidentil',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'is_insidentil' => 'boolean',
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    public function subJenisPajak(): BelongsTo
    {
        return $this->belongsTo(SubJenisPajak::class, 'sub_jenis_pajak_id');
    }

    public function reklameTariffs(): HasMany
    {
        return $this->hasMany(ReklameTariff::class, 'harga_patokan_reklame_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSubJenisPajak($query, ?string $subJenisPajakId)
    {
        return $query->when($subJenisPajakId, fn ($builder) => $builder->where('sub_jenis_pajak_id', $subJenisPajakId));
    }
}