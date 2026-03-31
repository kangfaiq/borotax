<?php

namespace App\Domain\Reklame\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReklameRequest extends Model
{
    use HasFactory, HasUuids, HasEncryptedAttributes;

    protected $table = 'reklame_requests';

    /**
     * Kolom yang dienkripsi
     */
    protected array $encryptedAttributes = [
        'user_nik',
        'user_name',
    ];

    protected $fillable = [
        'tax_object_id',
        'user_id',
        'user_nik',
        'user_name',
        'tanggal_pengajuan',
        'durasi_perpanjangan_hari',
        'catatan_pengajuan',
        'status',
        'tanggal_diproses',
        'petugas_id',
        'petugas_nama',
        'tanggal_selesai',
        'catatan_petugas',
        'skpd_id',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'tanggal_diproses' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'durasi_perpanjangan_hari' => 'integer',
    ];

    /**
     * Get reklame object (tax_objects via ReklameObject scope)
     */
    public function reklameObject(): BelongsTo
    {
        return $this->belongsTo(ReklameObject::class, 'tax_object_id');
    }

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get petugas
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Get SKPD
     */
    public function skpdReklame(): HasOne
    {
        return $this->hasOne(SkpdReklame::class, 'request_id');
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
        return $query->whereIn('status', ['diajukan', 'menungguVerifikasi']);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'diajukan' => 'Diajukan',
            'menungguVerifikasi' => 'Menunggu Verifikasi',
            'diproses' => 'Sedang Diproses',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => $this->status,
        };
    }
}
