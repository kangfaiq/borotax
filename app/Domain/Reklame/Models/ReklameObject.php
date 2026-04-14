<?php

namespace App\Domain\Reklame\Models;

use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Reklame\Models\HargaPatokanReklame;
use App\Domain\Reklame\Models\KelompokLokasiJalan;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Objek Pajak Reklame.
 *
 * Setelah konsolidasi, model ini menggunakan tabel `tax_objects`
 * dengan global scope filter jenis_pajak kode Reklame (41104).
 * Field alias (nama_reklame, alamat_reklame, foto_url) tetap tersedia
 * via accessor/mutator untuk backward compatibility.
 */
class ReklameObject extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes;
    protected $table = 'tax_objects';

    /**
     * Kolom yang dienkripsi (nama kolom sesuai tax_objects)
     */
    protected array $encryptedAttributes = [
        'nik',
        'nama_objek_pajak',
        'alamat_objek',
        'foto_objek_path',
    ];

    protected $fillable = [
        'nik',
        'nik_hash',
        'nama_objek_pajak',
        'jenis_pajak_id',
        'sub_jenis_pajak_id',
        'harga_patokan_reklame_id',
        'npwpd',
        'nopd',
        'alamat_objek',
        'kelurahan',
        'kecamatan',
        'panjang',
        'lebar',
        'tinggi',
        'sisi_atas',
        'sisi_bawah',
        'diameter',
        'diameter2',
        'alas',
        'bentuk',
        'luas_m2',
        'jumlah_muka',
        'tanggal_pasang',
        'masa_berlaku_sampai',
        'status',
        'kelompok_lokasi',
        'lokasi_jalan_id',
        'latitude',
        'longitude',
        'foto_objek_path',
        'tarif_persen',
        'tanggal_daftar',
        'is_active',
        'is_opd',
        // Alias names for backward compatibility (handled by mutators)
        'nama_reklame',
        'alamat_reklame',
        'foto_url',
    ];

    protected $casts = [
        'panjang' => 'decimal:2',
        'lebar' => 'decimal:2',
        'tinggi' => 'decimal:2',
        'sisi_atas' => 'decimal:2',
        'sisi_bawah' => 'decimal:2',
        'diameter' => 'decimal:2',
        'alas' => 'decimal:2',
        'luas_m2' => 'decimal:2',
        'jumlah_muka' => 'integer',
        'tanggal_pasang' => 'date',
        'masa_berlaku_sampai' => 'date',
        'tanggal_daftar' => 'date',
        'nopd' => 'integer',
        'tarif_persen' => 'decimal:2',
        'is_active' => 'boolean',
        'is_opd' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Cache jenis pajak ID per request
     */
    protected static ?string $cachedJenisPajakId = null;

    protected static function resolveJenisPajakId(): ?string
    {
        if (static::$cachedJenisPajakId && JenisPajak::where('id', static::$cachedJenisPajakId)->exists()) {
            return static::$cachedJenisPajakId;
        }

        static::$cachedJenisPajakId = JenisPajak::where('kode', '41104')->value('id') ?? null;

        return static::$cachedJenisPajakId;
    }

    /**
     * Boot: global scope + auto-set jenis_pajak + hitung luas
     */
    protected static function booted()
    {
        // Global scope: hanya objek reklame
        static::addGlobalScope('reklame', function (Builder $query) {
            $jenisPajakId = static::resolveJenisPajakId();

            if ($jenisPajakId) {
                $query->where($query->getModel()->getTable() . '.jenis_pajak_id', $jenisPajakId);
            }
        });

        // Auto-set jenis_pajak saat create
        static::creating(function ($model) {
            if (!$model->jenis_pajak_id) {
                $model->jenis_pajak_id = static::resolveJenisPajakId();
            }
        });

        static::saving(function ($model) {
            // Auto calculate luas berdasarkan bentuk
            $model->luas_m2 = $model->hitungLuas();
        });
    }

    // ── Field Aliases (backward compatibility) ──────────────

    public function getNamaReklameAttribute()
    {
        return $this->nama_objek_pajak;
    }

    public function setNamaReklameAttribute($value)
    {
        $this->nama_objek_pajak = $value;
    }

    public function getAlamatReklameAttribute()
    {
        return $this->alamat_objek;
    }

    public function setAlamatReklameAttribute($value)
    {
        $this->alamat_objek = $value;
    }

    public function getFotoUrlAttribute()
    {
        return $this->foto_objek_path;
    }

    public function setFotoUrlAttribute($value)
    {
        $this->foto_objek_path = $value;
    }

    /**
     * Alias untuk Filament resource: reklame_name
     */
    public function getReklameNameAttribute()
    {
        return $this->nama_objek_pajak;
    }

    /**
     * Alias untuk Filament resource: address
     */
    public function getAddressAttribute()
    {
        return $this->alamat_objek;
    }

    // ── Relations ───────────────────────────────────────────

    public function jenisPajak(): BelongsTo
    {
        return $this->belongsTo(JenisPajak::class, 'jenis_pajak_id');
    }

    public function subJenisPajak(): BelongsTo
    {
        return $this->belongsTo(SubJenisPajak::class, 'sub_jenis_pajak_id');
    }

    public function hargaPatokanReklame(): BelongsTo
    {
        return $this->belongsTo(HargaPatokanReklame::class, 'harga_patokan_reklame_id');
    }

    public function lokasiJalan(): BelongsTo
    {
        return $this->belongsTo(KelompokLokasiJalan::class, 'lokasi_jalan_id');
    }

    public function reklameRequests(): HasMany
    {
        return $this->hasMany(ReklameRequest::class, 'tax_object_id');
    }

    public function skpdReklame(): HasMany
    {
        return $this->hasMany(SkpdReklame::class, 'tax_object_id');
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAktif($query)
    {
        static::syncExpiredStatuses();

        return $query->where('status', 'aktif');
    }

    public function scopeKadaluarsa($query)
    {
        static::syncExpiredStatuses();

        return $query->where('status', 'kadaluarsa')
            ->orWhere('masa_berlaku_sampai', '<', now());
    }

    public function scopeByNik($query, string $nik)
    {
        return $query->where('nik_hash', self::generateHash($nik));
    }

    // ── Helpers ─────────────────────────────────────────────

    public function getStatusAttribute($value): ?string
    {
        if ($value === 'aktif') {
            $this->syncExpiredStatus();

            return $this->attributes['status'] ?? $value;
        }

        return $value;
    }

    public static function syncExpiredStatuses(): int
    {
        $updated = 0;

        static::query()
            ->where('status', 'aktif')
            ->whereDate('masa_berlaku_sampai', '<', today())
            ->get()
            ->each(function (self $reklameObject) use (&$updated): void {
                if ($reklameObject->syncExpiredStatus()) {
                    $updated++;
                }
            });

        return $updated;
    }

    public function syncExpiredStatus(): bool
    {
        if (! $this->shouldSyncExpiredStatus()) {
            return false;
        }

        $this->update([
            'status' => 'kadaluarsa',
        ]);

        return true;
    }

    protected function shouldSyncExpiredStatus(): bool
    {
        return $this->exists
            && $this->getRawOriginal('status') === 'aktif'
            && $this->masa_berlaku_sampai?->lt(today());
    }

    public function isKadaluarsa(): bool
    {
        return $this->masa_berlaku_sampai && $this->masa_berlaku_sampai < now();
    }

    public function getSisaHariAttribute(): int
    {
        if (!$this->masa_berlaku_sampai) {
            return 0;
        }
        return max(0, now()->diffInDays($this->masa_berlaku_sampai, false));
    }

    /**
     * Hitung luas berdasarkan bentuk reklame.
     *
     * - persegi: panjang × lebar
     * - trapesium: ((sisi_atas + sisi_bawah) / 2) × tinggi
     * - lingkaran: π × (diameter / 2)²
     * - segitiga: (alas × tinggi) / 2
     */
    public function hitungLuas(): float
    {
        return match ($this->bentuk) {
            'trapesium' => (((float) $this->sisi_atas + (float) $this->sisi_bawah) / 2) * (float) $this->tinggi,
            'lingkaran' => M_PI * pow((float) $this->diameter / 2, 2),
            'segitiga'  => ((float) $this->alas * (float) $this->tinggi) / 2,
            default     => (float) $this->panjang * (float) $this->lebar, // persegi
        };
    }

    public function getUkuranFormattedAttribute(): string
    {
        return match ($this->bentuk) {
            'trapesium' => "Trapesium: {$this->sisi_atas}m + {$this->sisi_bawah}m × {$this->tinggi}m ({$this->luas_m2} m²)",
            'lingkaran' => "Lingkaran: ⌀{$this->diameter}m ({$this->luas_m2} m²)",        'elips'     => "Elips: ⌀{$this->diameter}m × ⌀{$this->diameter2}m ({$this->luas_m2} m²)",            'segitiga'  => "Segitiga: {$this->alas}m × {$this->tinggi}m ({$this->luas_m2} m²)",
            default     => "{$this->panjang}m × {$this->lebar}m ({$this->luas_m2} m²)",
        };
    }

    public function getAlamatLengkapAttribute(): string
    {
        return "{$this->alamat_objek}, Kel. {$this->kelurahan}, Kec. {$this->kecamatan}";
    }
}
