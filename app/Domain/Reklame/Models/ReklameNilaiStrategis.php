<?php

namespace App\Domain\Reklame\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tarif nilai strategis reklame.
 *
 * Hanya berlaku untuk Reklame Tetap dengan luas ≥ 10 m².
 * Hanya untuk satuan perTahun dan perBulan.
 *
 * Kelas kelompok:
 * - A = kelompok A, A1, A2, A3
 * - B = kelompok B
 * - C = kelompok C
 */
class ReklameNilaiStrategis extends Model
{
    use SoftDeletes, HasUuids;
    protected $table = 'reklame_nilai_strategis';

    protected $fillable = [
        'kelas_kelompok',
        'luas_min',
        'luas_max',
        'tarif_per_tahun',
        'tarif_per_bulan',
        'is_active',
        'berlaku_mulai',
        'berlaku_sampai',
    ];

    protected $casts = [
        'luas_min' => 'decimal:2',
        'luas_max' => 'decimal:2',
        'tarif_per_tahun' => 'decimal:2',
        'tarif_per_bulan' => 'decimal:2',
        'is_active' => 'boolean',
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
    ];

    // ── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBerlakuPada($query, ?string $tanggal = null)
    {
        $tanggal = $tanggal ?? now()->toDateString();

        return $query
            ->where(function ($builder) use ($tanggal) {
                $builder->whereNull('berlaku_mulai')
                    ->orWhere('berlaku_mulai', '<=', $tanggal);
            })
            ->where(function ($builder) use ($tanggal) {
                $builder->whereNull('berlaku_sampai')
                    ->orWhere('berlaku_sampai', '>=', $tanggal);
            });
    }

    /**
     * Hitung total nilai strategis.
     *
     * @param string $kelompokLokasi Kelompok A/A1/A2/A3/B/C
     * @param float  $luasM2         Luas reklame (m²)
     * @param string $satuanWaktu    perTahun atau perBulan
     * @param int    $durasi         Jumlah satuan waktu
     * @param int    $jumlahReklame  Jumlah unit reklame
     * @return float Total nilai strategis (0 jika tidak berlaku)
     */
    public static function hitungNilaiStrategis(
        string $kelompokLokasi,
        float $luasM2,
        string $satuanWaktu,
        int $durasi,
        int $jumlahReklame,
        ?string $tanggal = null
    ): float {
        // Hanya berlaku untuk luas ≥ 10 m²
        if ($luasM2 < 10) {
            return 0;
        }

        // Hanya berlaku untuk perTahun atau perBulan
        if (!in_array($satuanWaktu, ['perTahun', 'perBulan'])) {
            return 0;
        }

        // Map kelompok → kelas
        $kelas = KelompokLokasiJalan::kelompokToKelas($kelompokLokasi);

        // Cari tarif NS
        $ns = static::where('kelas_kelompok', $kelas)
            ->where('luas_min', '<=', $luasM2)
            ->where(function ($q) use ($luasM2) {
                $q->whereNull('luas_max')
                    ->orWhere('luas_max', '>=', $luasM2);
            })
            ->where('is_active', true)
            ->berlakuPada($tanggal)
            ->orderByDesc('berlaku_mulai')
            ->first();

        if (!$ns) {
            return 0;
        }

        $tarifNs = $satuanWaktu === 'perTahun'
            ? (float) $ns->tarif_per_tahun
            : (float) $ns->tarif_per_bulan;

        return $tarifNs * $durasi * $jumlahReklame;
    }
}
