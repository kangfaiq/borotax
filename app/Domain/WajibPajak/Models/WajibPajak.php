<?php

namespace App\Domain\WajibPajak\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Village;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use App\Domain\Tax\Models\TaxObject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WajibPajak extends Model
{
    use HasFactory, HasUuids, HasEncryptedAttributes;

    protected $table = 'wajib_pajak';

    /**
     * Kolom yang dienkripsi
     */
    protected array $encryptedAttributes = [
        'nik',
        'nama_lengkap',
        'alamat',
        'nib',
        'npwp_pusat',
        'nama_perusahaan',
        'ktp_image_path',
        'selfie_image_path',
    ];

    protected $fillable = [
        'user_id',
        'nik',
        'nik_hash',
        'nama_lengkap',
        'alamat',
        'asal_wilayah',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
        'tipe_wajib_pajak',
        'nib',
        'npwp_pusat',
        'nama_perusahaan',
        'ktp_image_path',
        'selfie_image_path',
        'status',
        'tanggal_daftar',
        'tanggal_verifikasi',
        'petugas_id',
        'petugas_nama',
        'catatan_verifikasi',
        'npwpd',
        'nopd',
    ];

    protected $casts = [
        'tanggal_daftar' => 'datetime',
        'tanggal_verifikasi' => 'datetime',
        'nopd' => 'integer',
    ];

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get petugas verifikator
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Get tax objects milik WP ini
     */
    public function taxObjects(): HasMany
    {
        return $this->hasMany(TaxObject::class, 'npwpd', 'npwpd');
    }

    /**
     * Get province
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    /**
     * Get regency
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'regency_code', 'code');
    }

    /**
     * Get district
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }

    /**
     * Get village
     */
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_code', 'code');
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk menunggu verifikasi
     */
    public function scopeMenungguVerifikasi($query)
    {
        return $query->where('status', 'menungguVerifikasi');
    }

    /**
     * Check if perorangan
     */
    public function isPerorangan(): bool
    {
        return $this->tipe_wajib_pajak === 'perorangan';
    }

    /**
     * Check if perusahaan
     */
    public function isPerusahaan(): bool
    {
        return $this->tipe_wajib_pajak === 'perusahaan';
    }

    /**
     * Generate NPWPD
     * Format: P1XXXXXXXXXXX (perorangan) atau P2XXXXXXXXXXX (perusahaan)
     * Total 13 karakter: prefix 2 char + 11 digit sequential
     *
     * @param string $tipe 'perorangan' atau 'perusahaan'
     */
    public static function generateNpwpd(string $tipe = 'perorangan'): string
    {
        $prefix = $tipe === 'perusahaan' ? 'P2' : 'P1';

        // Ambil NPWPD terakhir dengan prefix yang sama
        $last = static::where('npwpd', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(npwpd, 3) AS UNSIGNED) DESC')
            ->value('npwpd');

        if ($last) {
            $lastNumber = (int) substr($last, 2); // ambil 11 digit setelah prefix
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 11, '0', STR_PAD_LEFT);
    }
}
