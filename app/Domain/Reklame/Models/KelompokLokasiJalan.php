<?php

namespace App\Domain\Reklame\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Referensi daftar jalan per kelompok lokasi reklame.
 *
 * Kelompok:
 * - A  = Jalan Utama/Protokol
 * - A1 = Jalan Sekunder Utama
 * - A2 = Jalan Sekunder
 * - A3 = Jalan Lokal Utama
 * - B  = Jalan Lokal
 * - C  = Jalan Lingkungan
 */
class KelompokLokasiJalan extends Model
{
    use SoftDeletes, HasUuids;
    protected $table = 'kelompok_lokasi_jalan';

    protected $fillable = [
        'kelompok',
        'nama_jalan',
        'deskripsi',
        'is_active',
        'berlaku_mulai',
        'berlaku_sampai',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
    ];

    /**
     * Mapping kelompok → kelas untuk perhitungan nilai strategis.
     */
    public static function kelompokToKelas(string $kelompok): string
    {
        return match ($kelompok) {
            'A', 'A1', 'A2', 'A3' => 'A',
            'B' => 'B',
            default => 'C',
        };
    }

    /**
     * Scope: hanya aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: filter by kelompok
     */
    public function scopeByKelompok($query, string $kelompok)
    {
        return $query->where('kelompok', $kelompok);
    }

    public function scopeBerlakuPada(Builder $query, ?string $tanggal = null): Builder
    {
        $tanggal = $tanggal ?? now()->toDateString();

        return $query
            ->where(function (Builder $builder) use ($tanggal) {
                $builder->where(function (Builder $inner) use ($tanggal) {
                    $inner->whereNotNull('berlaku_mulai')
                        ->where('berlaku_mulai', '<=', $tanggal)
                        ->where(function (Builder $end) use ($tanggal) {
                            $end->whereNull('berlaku_sampai')
                                ->orWhere('berlaku_sampai', '>=', $tanggal);
                        });
                })->orWhereNull('berlaku_mulai');
            });
    }

    public static function getActiveOptions(?string $tanggal = null, ?string $selectedId = null): array
    {
        $query = static::query()
            ->where(function (Builder $builder) use ($tanggal, $selectedId) {
                $builder->where(function (Builder $active) use ($tanggal) {
                    $active->active()->berlakuPada($tanggal);
                });

                if ($selectedId) {
                    $builder->orWhere('id', $selectedId);
                }
            })
            ->orderBy('kelompok')
            ->orderBy('nama_jalan')
            ->orderByDesc('berlaku_mulai')
            ->get();

        return $query->mapWithKeys(function (self $item) {
            $periode = $item->berlaku_mulai?->format('d/m/Y') ?? '-';
            $berakhir = $item->berlaku_sampai?->format('d/m/Y') ?? 'seterusnya';

            return [
                $item->id => "{$item->nama_jalan} (Kelompok {$item->kelompok}, {$periode} - {$berakhir})",
            ];
        })->toArray();
    }

    /**
     * Get daftar kelompok dengan deskripsi untuk dropdown.
     */
    public static function getKelompokOptions(): array
    {
        return [
            'A'  => 'A — Jalan Utama/Protokol',
            'A1' => 'A1 — Jalan Sekunder Utama',
            'A2' => 'A2 — Jalan Sekunder',
            'A3' => 'A3 — Jalan Lokal Utama',
            'B'  => 'B — Jalan Lokal',
            'C'  => 'C — Jalan Lingkungan',
        ];
    }
}
