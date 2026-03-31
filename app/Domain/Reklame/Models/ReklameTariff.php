<?php

namespace App\Domain\Reklame\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tarif pokok reklame per sub jenis pajak × kelompok lokasi × satuan waktu.
 *
 * Tarif sudah termasuk pajak 25%, sehingga PAJAK = POKOK TOTAL langsung
 * tanpa dikalikan 25% lagi.
 *
 * Mendukung versioning tarif melalui kolom berlaku_mulai & berlaku_sampai.
 *
 * - Reklame Tetap: tarif berbeda per kelompok lokasi (A/A1/A2/A3/B/C)
 * - Reklame Insidentil: tarif tunggal (kelompok_lokasi = null)
 */
class ReklameTariff extends Model
{
    use HasUuids;

    protected $table = 'reklame_tariffs';

    protected $fillable = [
        'harga_patokan_reklame_id',
        'kelompok_lokasi',
        'satuan_waktu',
        'satuan_label',
        'nspr',
        'njopr',
        'tarif_pokok',
        'is_active',
        'berlaku_mulai',
        'berlaku_sampai',
    ];

    protected $casts = [
        'nspr' => 'decimal:2',
        'njopr' => 'decimal:2',
        'tarif_pokok' => 'decimal:2',
        'is_active' => 'boolean',
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
    ];

    // ── Relations ───────────────────────────────────────────

    public function hargaPatokanReklame(): BelongsTo
    {
        return $this->belongsTo(HargaPatokanReklame::class, 'harga_patokan_reklame_id');
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: hanya tarif yang berlaku pada tanggal tertentu.
     */
    public function scopeBerlakuPada($query, ?string $tanggal = null)
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
     * Lookup tarif pokok berdasarkan sub jenis, kelompok, satuan, dan tanggal.
     *
     * Untuk insidentil (kelompok_lokasi null di DB), kelompok param diabaikan.
     * Jika ada beberapa tarif yang overlap, ambil yang berlaku_mulai paling baru.
     *
     * @param string      $hargaPatokanReklameId
     * @param string|null $kelompokLokasi
     * @param string      $satuanWaktu
     * @param string|null $tanggal  Format Y-m-d. Null = hari ini.
     */
    public static function lookupTarif(
        string $hargaPatokanReklameId,
        ?string $kelompokLokasi,
        string $satuanWaktu,
        ?string $tanggal = null
    ): ?float {
        $record = static::lookupRecord($hargaPatokanReklameId, $kelompokLokasi, $satuanWaktu, $tanggal);
        return $record ? (float) $record->tarif_pokok : null;
    }

    /**
     * Lookup full tariff record (termasuk NSPR, NJOPR, satuan_label).
     */
    public static function lookupRecord(
        string $hargaPatokanReklameId,
        ?string $kelompokLokasi,
        string $satuanWaktu,
        ?string $tanggal = null
    ): ?self {
        $tanggal = $tanggal ?? now()->toDateString();

        // Coba cari dengan kelompok spesifik (reklame tetap)
        $record = static::where('harga_patokan_reklame_id', $hargaPatokanReklameId)
            ->where('kelompok_lokasi', $kelompokLokasi)
            ->where('satuan_waktu', $satuanWaktu)
            ->where('is_active', true)
            ->berlakuPada($tanggal)
            ->orderByDesc('berlaku_mulai')
            ->first();

        if ($record) {
            return $record;
        }

        // Fallback: cari tarif tunggal (insidentil, kelompok_lokasi = null)
        return static::where('harga_patokan_reklame_id', $hargaPatokanReklameId)
            ->whereNull('kelompok_lokasi')
            ->where('satuan_waktu', $satuanWaktu)
            ->where('is_active', true)
            ->berlakuPada($tanggal)
            ->orderByDesc('berlaku_mulai')
            ->first();
    }

    /**
     * Get satuan waktu yang tersedia untuk sub jenis tertentu.
     */
    public static function getAvailableSatuanWaktu(string $hargaPatokanReklameId, ?string $kelompokLokasi = null, ?string $tanggal = null): array
    {
        $query = static::where('harga_patokan_reklame_id', $hargaPatokanReklameId)
            ->where('is_active', true)
            ->berlakuPada($tanggal);

        if ($kelompokLokasi) {
            $query->where('kelompok_lokasi', $kelompokLokasi);
        } else {
            $query->whereNull('kelompok_lokasi');
        }

        $records = $query->select('satuan_waktu', 'satuan_label', 'tarif_pokok')
            ->distinct()
            ->get();

        // Fallback: jika tidak ada record dengan kelompok_lokasi null, coba tanpa filter
        if ($records->isEmpty() && !$kelompokLokasi) {
            $records = static::where('harga_patokan_reklame_id', $hargaPatokanReklameId)
                ->where('is_active', true)
                ->berlakuPada($tanggal)
                ->select('satuan_waktu', 'satuan_label', 'tarif_pokok')
                ->distinct()
                ->get();
        }

        // Urutkan dari terlama ke terendah
        $order = ['perTahun' => 1, 'perBulan' => 2, 'perMinggu' => 3, 'perMingguPerBuah' => 4, 'perHari' => 5, 'perHariPerBuah' => 6, 'perLembar' => 7];

        return $records->sortBy(fn ($item) => $order[$item->satuan_waktu] ?? 99)
            ->mapWithKeys(function ($item) {
                $label = $item->satuan_label;
                if ($item->tarif_pokok !== null) {
                    $label .= ' — Rp ' . number_format($item->tarif_pokok, 0, ',', '.');
                }
                return [$item->satuan_waktu => $label];
            })->toArray();
    }

    /**
     * Label display satuan waktu.
     */
    public static function getSatuanWaktuLabels(): array
    {
        return [
            'perTahun' => 'Per Tahun',
            'perBulan' => 'Per Bulan',
            'perMinggu' => 'Per Minggu',
            'perHari' => 'Per Hari',
            'perLembar' => 'Per Lembar',
            'perMingguPerBuah' => 'Per Minggu/Buah',
            'perHariPerBuah' => 'Per Hari/Buah',
        ];
    }
}
