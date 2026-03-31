<?php

namespace App\Domain\AirTanah\Models;

use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NpaAirTanah extends Model
{
    use HasFactory, HasUuids, HasEncryptedAttributes;

    protected $table = 'npa_air_tanah';

    // npa_tiers dihapus dulu dari encrypt secara otomatis karena MySQL butuh valid JSON sebelum encrypt (karena tipe datanya DB adalah JSON). 
    // Sebagai gantinya, karena npa_tiers ini bukan data PII sensitif (hanya harga publik), kita bypass saja encryption untuk npa_tiers agar bisa pakai fungsi JSON query di database.
    protected array $encryptedAttributes = [
        'npa_per_m3',
    ];

    protected $fillable = [
        'kelompok_pemakaian',
        'kriteria_sda',
        'npa_per_m3',
        'npa_tiers',
        'berlaku_mulai',
        'berlaku_sampai',
        'dasar_hukum',
        'is_active',
    ];

    protected $casts = [
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
        'is_active' => 'boolean',
        'npa_tiers' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: berlaku pada tanggal tertentu.
     */
    public function scopeBerlakuPada(Builder $query, ?string $tanggal = null): Builder
    {
        $tanggal = $tanggal ?: now()->toDateString();

        return $query->where('berlaku_mulai', '<=', $tanggal)
            ->where(function ($q) use ($tanggal) {
                $q->whereNull('berlaku_sampai')
                    ->orWhere('berlaku_sampai', '>=', $tanggal);
            });
    }

    /**
     * Mapping kode pendek → label lengkap untuk kelompok_pemakaian.
     */
    public const KELOMPOK_PEMAKAIAN_MAP = [
        '1' => 'Kelompok 1',
        '2' => 'Kelompok 2',
        '3' => 'Kelompok 3',
        '4' => 'Kelompok 4',
        '5' => 'Kelompok 5',
    ];

    /**
     * Mapping kode pendek → label lengkap untuk kriteria_sda.
     */
    public const KRITERIA_SDA_MAP = [
        '1' => 'Air Tanah Kualitas Baik, Ada Sumber Alternatif',
        '2' => 'Air Tanah Kualitas Baik, Tidak Ada Sumber Alternatif',
        '3' => 'Air Tanah Kualitas Tidak Baik, Ada Sumber Alternatif',
        '4' => 'Air Tanah Kualitas Tidak Baik, Tidak Ada Sumber Alternatif',
    ];

    /**
     * Resolve kode pendek ke label yang dipakai di tabel npa_air_tanah.
     */
    public static function resolveKelompok(string $kode): string
    {
        return self::KELOMPOK_PEMAKAIAN_MAP[$kode] ?? $kode;
    }

    public static function resolveKriteria(string $kode): string
    {
        return self::KRITERIA_SDA_MAP[$kode] ?? $kode;
    }

    public static function lookup(string $kelompokPemakaian, string $kriteriaSda, $tanggal = null): ?float
    {
        $kelompok = static::resolveKelompok($kelompokPemakaian);
        $kriteria = static::resolveKriteria($kriteriaSda);

        $record = static::active()
            ->berlakuPada($tanggal)
            ->where('kelompok_pemakaian', $kelompok)
            ->where('kriteria_sda', $kriteria)
            ->first();

        if (! $record) {
            return null;
        }

        if ($record->npa_per_m3 !== null) {
            return (float) $record->npa_per_m3;
        }

        $tier = collect($record->npa_tiers)->firstWhere('min_vol', 0);

        return $tier ? (float) ($tier['npa'] ?? null) : null;
    }

    /**
     * Lookup NPA array (tiers) berdasarkan kelompok + kriteria + tanggal.
     */
    public static function lookupTiers(string $kelompokPemakaian, string $kriteriaSda, $tanggal = null): ?array
    {
        $kelompok = static::resolveKelompok($kelompokPemakaian);
        $kriteria = static::resolveKriteria($kriteriaSda);

        $record = static::active()
            ->berlakuPada($tanggal)
            ->where('kelompok_pemakaian', $kelompok)
            ->where('kriteria_sda', $kriteria)
            ->first();

        // Fallback untuk backward compatibility jika tier JSON kosong namun per_m3 ada isinya (flat rate)
        if ($record && empty($record->npa_tiers) && $record->npa_per_m3) {
             return [
                 [
                     'min_vol' => 0,
                     'max_vol' => 99999999, // unlimited
                     'npa' => (float) $record->npa_per_m3
                 ]
             ];
        }

        return $record ? $record->npa_tiers : null;
    }
}
