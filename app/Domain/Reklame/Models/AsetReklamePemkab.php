<?php

namespace App\Domain\Reklame\Models;

use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsetReklamePemkab extends Model
{
    use HasFactory, HasUuids, HasEncryptedAttributes;

    protected $table = 'aset_reklame_pemkab';

    protected array $encryptedAttributes = [
        'lokasi',
        'harga_sewa_per_tahun',
        'harga_sewa_per_bulan',
        'harga_sewa_per_minggu',
        'foto_path',
    ];

    protected $fillable = [
        'kode_aset',
        'nama',
        'jenis',
        'lokasi',
        'keterangan',
        'kawasan',
        'traffic',
        'kelompok_lokasi',
        'panjang',
        'lebar',
        'luas_m2',
        'jumlah_muka',
        'latitude',
        'longitude',
        'harga_sewa_per_tahun',
        'harga_sewa_per_bulan',
        'harga_sewa_per_minggu',
        'foto_path',
        'status_ketersediaan',
        'catatan_status',
        'is_active',
        'peminjam_opd',
        'materi_pinjam',
        'pinjam_mulai',
        'pinjam_selesai',
        'catatan_pinjam',
    ];

    protected $casts = [
        'panjang'   => 'decimal:2',
        'lebar'     => 'decimal:2',
        'luas_m2'   => 'decimal:2',
        'jumlah_muka' => 'integer',
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
        'pinjam_mulai' => 'date',
        'pinjam_selesai' => 'date',
    ];

    // ── Relations ───────────────────────────────────────────

    public function permohonanSewa(): HasMany
    {
        return $this->hasMany(PermohonanSewaReklame::class, 'aset_reklame_pemkab_id');
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(PeminjamanAsetReklame::class, 'aset_reklame_pemkab_id');
    }

    public function peminjamanAktif(): HasMany
    {
        return $this->peminjaman()->where('status', 'aktif');
    }

    public function skpdReklame(): HasMany
    {
        return $this->hasMany(SkpdReklame::class, 'aset_reklame_pemkab_id');
    }

    public function kelompokLokasiJalan(): BelongsTo
    {
        return $this->belongsTo(KelompokLokasiJalan::class, 'kelompok_lokasi', 'kode');
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopeTersedia($query)
    {
        return $query->where('status_ketersediaan', 'tersedia')->where('is_active', true);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByJenis($query, string $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    // ── Helpers ─────────────────────────────────────────────

    /**
     * Cek apakah aset tersedia untuk disewa
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->status_ketersediaan === 'tersedia';
    }

    /**
     * Sinkronisasi status ketersediaan berdasarkan SKPD aktif.
     * Status 'maintenance' dan 'tidak_aktif' tidak akan ditimpa.
     */
    public function syncKetersediaan(): void
    {
        // Jangan override status manual
        if (in_array($this->status_ketersediaan, ['maintenance', 'tidak_aktif', 'dipinjam_opd'])) {
            return;
        }

        $hasActiveSkpd = $this->skpdReklame()
            ->where('status', 'disetujui')
            ->where('masa_berlaku_sampai', '>=', now()->toDateString())
            ->exists();

        $newStatus = $hasActiveSkpd ? 'disewa' : 'tersedia';

        if ($this->status_ketersediaan !== $newStatus) {
            $this->update([
                'status_ketersediaan' => $newStatus,
                'catatan_status' => $hasActiveSkpd ? 'Otomatis: SKPD aktif' : 'Otomatis: semua SKPD expired',
            ]);
        }
    }

    /**
     * Get label status untuk tampilan
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_ketersediaan) {
            'tersedia'     => 'Tersedia',
            'disewa'       => 'Disewa',
            'maintenance'  => 'Maintenance',
            'tidak_aktif'  => 'Tidak Aktif',
            'dipinjam_opd' => 'Dipinjam OPD',
            default        => $this->status_ketersediaan,
        };
    }

    /**
     * Get badge color untuk tampilan
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status_ketersediaan) {
            'tersedia'     => 'success',
            'disewa'       => 'danger',
            'maintenance'  => 'warning',
            'tidak_aktif'  => 'gray',
            'dipinjam_opd' => 'info',
            default        => 'gray',
        };
    }

    public function getUkuranFormattedAttribute(): string
    {
        return "{$this->panjang}m × {$this->lebar}m ({$this->luas_m2} m²)";
    }
}
