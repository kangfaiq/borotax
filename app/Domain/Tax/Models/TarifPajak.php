<?php

namespace App\Domain\Tax\Models;

use App\Domain\Master\Models\SubJenisPajak;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat tarif persentase pajak per sub jenis pajak.
 *
 * Setiap sub jenis pajak bisa punya beberapa record tarif dengan masa berlaku berbeda.
 * Saat buat billing/SKPD, sistem lookup tarif yang berlaku berdasarkan tanggal masa pajak.
 */
class TarifPajak extends Model
{
    use HasUuids;

    protected $table = 'tarif_pajak';

    protected $fillable = [
        'sub_jenis_pajak_id',
        'tarif_persen',
        'berlaku_mulai',
        'berlaku_sampai',
        'dasar_hukum',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'tarif_persen' => 'decimal:2',
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
        'is_active' => 'boolean',
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
     * Scope: hanya tarif yang berlaku pada tanggal tertentu.
     */
    public function scopeBerlakuPada(Builder $query, ?string $tanggal = null): Builder
    {
        $tanggal = $tanggal ?? now()->toDateString();

        return $query
            ->where('berlaku_mulai', '<=', $tanggal)
            ->where(function ($q) use ($tanggal) {
                $q->whereNull('berlaku_sampai')
                    ->orWhere('berlaku_sampai', '>=', $tanggal);
            });
    }

    /**
     * Lookup tarif persen berdasarkan sub_jenis_pajak_id dan tanggal.
     *
     * Jika ada beberapa tarif yang overlap, ambil yang berlaku_mulai paling baru.
     */
    public static function lookup(string $subJenisPajakId, $tanggal = null): ?float
    {
        $record = static::active()
            ->berlakuPada($tanggal)
            ->where('sub_jenis_pajak_id', $subJenisPajakId)
            ->orderByDesc('berlaku_mulai')
            ->first();

        return $record ? (float) $record->tarif_persen : null;
    }

    /**
     * Lookup tarif beserta dasar hukum.
     */
    public static function lookupWithDasarHukum(string $subJenisPajakId, $tanggal = null): ?array
    {
        $record = static::active()
            ->berlakuPada($tanggal)
            ->where('sub_jenis_pajak_id', $subJenisPajakId)
            ->orderByDesc('berlaku_mulai')
            ->first();

        if (!$record) {
            return null;
        }

        return [
            'tarif_persen' => (float) $record->tarif_persen,
            'dasar_hukum' => $record->dasar_hukum,
            'berlaku_mulai' => $record->berlaku_mulai?->format('Y-m-d'),
        ];
    }
}
