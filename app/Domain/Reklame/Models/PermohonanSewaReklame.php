<?php

namespace App\Domain\Reklame\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PermohonanSewaReklame extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes;
    protected $table = 'permohonan_sewa_reklame';

    protected array $encryptedAttributes = [
        'nik',
        'nama',
        'alamat',
        'no_telepon',
        'nama_usaha',
        'email',
    ];

    protected $fillable = [
        'aset_reklame_pemkab_id',
        'user_id',
        'nomor_tiket',
        'nik',
        'nama',
        'alamat',
        'no_telepon',
        'email',
        'nama_usaha',
        'nomor_registrasi_izin',
        'jenis_reklame_dipasang',
        'durasi_sewa_hari',
        'satuan_sewa',
        'tanggal_mulai_diinginkan',
        'catatan',
        'file_ktp',
        'file_npwp',
        'file_desain_reklame',
        'status',
        'tanggal_pengajuan',
        'petugas_id',
        'petugas_nama',
        'catatan_petugas',
        'tanggal_diproses',
        'tanggal_selesai',
        'skpd_id',
        'npwpd',
    ];

    protected $casts = [
        'durasi_sewa_hari' => 'integer',
        'tanggal_mulai_diinginkan' => 'date',
        'tanggal_pengajuan' => 'datetime',
        'tanggal_diproses' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    // ── Boot ────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->nomor_tiket)) {
                $today = now()->format('Ymd');
                $last  = static::where('nomor_tiket', 'like', "SEWA-{$today}-%")
                    ->orderByDesc('nomor_tiket')
                    ->value('nomor_tiket');

                $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
                $model->nomor_tiket = "SEWA-{$today}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ── Relations ───────────────────────────────────────────

    public function asetReklame(): BelongsTo
    {
        return $this->belongsTo(AsetReklamePemkab::class, 'aset_reklame_pemkab_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function skpdReklame(): HasOne
    {
        return $this->hasOne(SkpdReklame::class, 'permohonan_sewa_id');
    }

    // ── Scopes ──────────────────────────────────────────────

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['diajukan', 'perlu_revisi', 'diproses']);
    }

    // ── Helpers ─────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'diajukan'     => 'Diajukan',
            'perlu_revisi' => 'Perlu Revisi',
            'diproses'     => 'Sedang Diproses',
            'disetujui'    => 'Disetujui',
            'ditolak'      => 'Ditolak',
            default        => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'diajukan'     => 'warning',
            'perlu_revisi' => 'info',
            'diproses'     => 'primary',
            'disetujui'    => 'success',
            'ditolak'      => 'danger',
            default        => 'gray',
        };
    }
}
