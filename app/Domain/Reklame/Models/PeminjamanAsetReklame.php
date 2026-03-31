<?php

namespace App\Domain\Reklame\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanAsetReklame extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'peminjaman_aset_reklame';

    protected $fillable = [
        'aset_reklame_pemkab_id',
        'peminjam_opd',
        'materi_pinjam',
        'pinjam_mulai',
        'pinjam_selesai',
        'catatan_pinjam',
        'file_bukti_dukung',
        'status',
        'petugas_id',
        'petugas_nama',
    ];

    protected $casts = [
        'pinjam_mulai'   => 'date',
        'pinjam_selesai' => 'date',
    ];

    // ── Relations ───────────────────────────────────────────

    public function asetReklame(): BelongsTo
    {
        return $this->belongsTo(AsetReklamePemkab::class, 'aset_reklame_pemkab_id');
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    // ── Helpers ─────────────────────────────────────────────

    public static function getStatusLabels(): array
    {
        return [
            'aktif'   => 'Aktif',
            'selesai' => 'Selesai',
        ];
    }
}
