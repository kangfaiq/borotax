<?php

namespace App\Domain\Reklame\Models;

use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsetReklamePemkab extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes;
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
        static::syncExpiredOpdBorrowings();

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

    public function getStatusKetersediaanAttribute($value): string
    {
        if ($value === 'dipinjam_opd') {
            $this->syncExpiredOpdBorrowing();

            return $this->attributes['status_ketersediaan'] ?? $value;
        }

        return $value;
    }

    public static function syncExpiredOpdBorrowings(): int
    {
        $updated = 0;

        static::query()
            ->where('is_active', true)
            ->where('status_ketersediaan', 'dipinjam_opd')
            ->whereDate('pinjam_selesai', '<', today())
            ->get()
            ->each(function (self $aset) use (&$updated): void {
                if ($aset->syncExpiredOpdBorrowing()) {
                    $updated++;
                }
            });

        return $updated;
    }

    public function syncExpiredOpdBorrowing(): bool
    {
        if (! $this->shouldAutoReleaseExpiredOpdBorrowing()) {
            return false;
        }

        $tanggalSelesai = $this->pinjam_selesai?->format('d-m-Y');

        $this->peminjamanAktif()->update(['status' => 'selesai']);

        $this->update([
            'status_ketersediaan' => 'tersedia',
            'catatan_status' => $tanggalSelesai
                ? 'Otomatis: masa pinjam OPD berakhir pada ' . $tanggalSelesai
                : 'Otomatis: masa pinjam OPD berakhir',
            'peminjam_opd' => null,
            'materi_pinjam' => null,
            'pinjam_mulai' => null,
            'pinjam_selesai' => null,
            'catatan_pinjam' => null,
        ]);

        return true;
    }

    protected function shouldAutoReleaseExpiredOpdBorrowing(): bool
    {
        return $this->exists
            && $this->getRawOriginal('status_ketersediaan') === 'dipinjam_opd'
            && $this->is_active
            && $this->pinjam_selesai?->lt(today());
    }

    /**
     * Sinkronisasi status ketersediaan berdasarkan SKPD aktif.
     * Status 'maintenance' dan 'tidak_aktif' tidak akan ditimpa.
     */
    public function syncKetersediaan(): void
    {
        if ($this->syncExpiredOpdBorrowing()) {
            return;
        }

        // Jangan override status manual
        if (in_array($this->getRawOriginal('status_ketersediaan'), ['maintenance', 'tidak_aktif', 'dipinjam_opd'], true)) {
            return;
        }

        $hasActiveSkpd = $this->skpdReklame()
            ->where('status', 'disetujui')
            ->where('masa_berlaku_sampai', '>=', now()->toDateString())
            ->exists();

        $newStatus = $hasActiveSkpd ? 'disewa' : 'tersedia';

        if ($this->getRawOriginal('status_ketersediaan') !== $newStatus) {
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
