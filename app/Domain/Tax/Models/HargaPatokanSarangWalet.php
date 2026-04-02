<?php

namespace App\Domain\Tax\Models;

use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HargaPatokanSarangWalet extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes;
    protected $table = 'harga_patokan_sarang_walet';

    protected array $encryptedAttributes = [
        'harga_patokan',
    ];

    protected $fillable = [
        'nama_jenis',
        'harga_patokan',
        'satuan',
        'dasar_hukum',
        'berlaku_mulai',
        'berlaku_sampai',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
    ];

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
