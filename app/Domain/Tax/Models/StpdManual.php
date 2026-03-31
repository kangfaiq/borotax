<?php

namespace App\Domain\Tax\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Shared\Traits\CalculatesJatuhTempo;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StpdManual extends Model
{
    use HasFactory, HasUuids, HasEncryptedAttributes, CalculatesJatuhTempo, SoftDeletes;

    protected $table = 'stpd_manuals';

    protected array $encryptedAttributes = [
        'sanksi_dihitung',
        'pokok_belum_dibayar',
    ];

    protected $fillable = [
        'tax_id',
        'tipe',
        'nomor_stpd',
        'status',
        'proyeksi_tanggal_bayar',
        'bulan_terlambat',
        'sanksi_dihitung',
        'pokok_belum_dibayar',
        'catatan_petugas',
        'catatan_verifikasi',
        'petugas_id',
        'petugas_nama',
        'tanggal_buat',
        'verifikator_id',
        'verifikator_nama',
        'tanggal_verifikasi',
        'pimpinan_id',
    ];

    protected $casts = [
        'proyeksi_tanggal_bayar' => 'date',
        'tanggal_buat' => 'datetime',
        'tanggal_verifikasi' => 'datetime',
        'bulan_terlambat' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(Pimpinan::class, 'pimpinan_id');
    }

    // ── Generate Nomor STPD ───────────────────────────────────────────────

    /**
     * Format: STPD/[TAHUN]/[BULAN]/[6 DIGIT]
     */
    public static function generateNomorStpd(): string
    {
        $tahun = date('Y');
        $bulan = date('m');
        $count = self::whereYear('tanggal_buat', $tahun)
            ->whereMonth('tanggal_buat', $bulan)
            ->count() + 1;
        $number = str_pad($count, 6, '0', STR_PAD_LEFT);

        return "STPD/{$tahun}/{$bulan}/{$number}";
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isDisetujui(): bool
    {
        return $this->status === 'disetujui';
    }

    public function isTipePokok(): bool
    {
        return $this->tipe === 'pokok_sanksi';
    }

    public function isTipeSanksi(): bool
    {
        return $this->tipe === 'sanksi_saja';
    }
}
