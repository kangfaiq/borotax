<?php

namespace App\Domain\AirTanah\Models;

use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Objek Pajak Air Tanah.
 *
 * Setelah konsolidasi, model ini menggunakan tabel `tax_objects`
 * dengan global scope filter jenis_pajak kode ABT (4.1.01.08).
 * Field alias (nama_objek) tetap tersedia via accessor/mutator
 * untuk backward compatibility.
 */
class WaterObject extends Model
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
        'jenis_sumber',
        'npwpd',
        'nopd',
        'alamat_objek',
        'kelurahan',
        'kecamatan',
        'latitude',
        'longitude',
        'last_meter_reading',
        'last_report_date',
        'tanggal_daftar',
        'is_active',
        'is_opd',
        'foto_objek_path',
        'tarif_persen',
        'kelompok_pemakaian',
        'kriteria_sda',
        'uses_meter',
        // Alias names for backward compatibility (handled by mutators)
        'nama_objek',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
        'last_report_date' => 'date',
        'is_active' => 'boolean',
        'is_opd' => 'boolean',
        'uses_meter' => 'boolean',
        'nopd' => 'integer',
        'last_meter_reading' => 'integer',
        'tarif_persen' => 'decimal:2',
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

        static::$cachedJenisPajakId = JenisPajak::where('kode', '41108')->value('id') ?? null;

        return static::$cachedJenisPajakId;
    }

    /**
     * Boot: global scope + auto-set jenis_pajak
     */
    protected static function booted()
    {
        // Global scope: hanya objek air tanah
        static::addGlobalScope('air_tanah', function (Builder $query) {
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
        });
    }

    // -- Field Aliases (backward compatibility) --

    public function getNamaObjekAttribute()
    {
        return $this->nama_objek_pajak;
    }

    public function setNamaObjekAttribute($value)
    {
        $this->nama_objek_pajak = $value;
    }

    /**
     * Alias untuk Filament resource: name
     */
    public function getNameAttribute()
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

    // -- Relations --

    public function jenisPajak(): BelongsTo
    {
        return $this->belongsTo(JenisPajak::class, 'jenis_pajak_id');
    }

    public function subJenisPajak(): BelongsTo
    {
        return $this->belongsTo(SubJenisPajak::class, 'sub_jenis_pajak_id');
    }

    public function meterReports(): HasMany
    {
        return $this->hasMany(MeterReport::class, 'tax_object_id');
    }

    public function skpdAirTanah(): HasMany
    {
        return $this->hasMany(SkpdAirTanah::class, 'tax_object_id');
    }

    // -- Scopes --

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByNik($query, string $nik)
    {
        return $query->where('nik_hash', self::generateHash($nik));
    }

    // -- Helpers --

    public function getJenisSumberLabelAttribute(): string
    {
        return match ($this->jenis_sumber) {
            'sumurBor' => 'Sumur Bor',
            'sumurGali' => 'Sumur Gali',
            'matAir' => 'Mata Air',
            'springWell' => 'Spring Well',
            default => $this->jenis_sumber ?? '-',
        };
    }

    public function getAlamatLengkapAttribute(): string
    {
        return "{$this->alamat_objek}, Kel. {$this->kelurahan}, Kec. {$this->kecamatan}";
    }
}
