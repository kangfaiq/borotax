<?php

namespace App\Domain\Tax\Models;

use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaPatokanMblb extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes;
    protected $table = 'harga_patokan_mblb';

    protected array $encryptedAttributes = [
        'harga_patokan',
    ];

    protected $fillable = [
        'sub_jenis_pajak_id',
        'nama_mineral',
        'nama_alternatif',
        'harga_patokan',
        'satuan',
        'dasar_hukum',
        'is_active',
        'keterangan',
        'berlaku_mulai',
        'berlaku_sampai',
    ];

    protected $casts = [
        'nama_alternatif' => 'array',
        'is_active' => 'boolean',
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
    ];

    public function subJenisPajak(): BelongsTo
    {
        return $this->belongsTo(SubJenisPajak::class, 'sub_jenis_pajak_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: hanya harga patokan yang berlaku pada tanggal tertentu.
     */
    public function scopeBerlakuPada(Builder $query, ?string $tanggal = null): Builder
    {
        $tanggal = $tanggal ?? now()->toDateString();

        return $query
            ->where(function ($q) use ($tanggal) {
                $q->where(function ($inner) use ($tanggal) {
                    $inner->whereNotNull('berlaku_mulai')
                        ->where('berlaku_mulai', '<=', $tanggal)
                        ->where(function ($end) use ($tanggal) {
                            $end->whereNull('berlaku_sampai')
                                ->orWhere('berlaku_sampai', '>=', $tanggal);
                        });
                })->orWhereNull('berlaku_mulai');
            });
    }

    public function getHargaPatokanNumericAttribute(): float
    {
        return (float) $this->harga_patokan;
    }
}
