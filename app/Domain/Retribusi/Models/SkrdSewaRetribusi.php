<?php

namespace App\Domain\Retribusi\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Retribusi\Models\ObjekRetribusiSewaTanah;
use App\Domain\Shared\Traits\CalculatesJatuhTempo;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SkrdSewaRetribusi extends Model
{
    use HasUuids, HasEncryptedAttributes, CalculatesJatuhTempo, SoftDeletes;

    protected $table = 'skrd_sewa_retribusi';

    protected array $encryptedAttributes = [
        'nik_wajib_pajak',
        'nama_wajib_pajak',
        'alamat_wajib_pajak',
        'nama_objek',
        'alamat_objek',
        'tarif_nominal',
        'jumlah_retribusi',
    ];

    protected $fillable = [
        'nomor_skrd',
        'jenis_pajak_id',
        'sub_jenis_pajak_id',
        'objek_retribusi_id',
        'npwpd',
        'nik_wajib_pajak',
        'nama_wajib_pajak',
        'alamat_wajib_pajak',
        'nama_objek',
        'alamat_objek',
        'tarif_nominal',
        'satuan_waktu',
        'satuan_label',
        'durasi',
        'luas_m2',
        'jumlah_reklame',
        'tarif_pajak_persen',
        'jumlah_retribusi',
        'masa_berlaku_mulai',
        'masa_berlaku_sampai',
        'jatuh_tempo',
        'status',
        'tanggal_buat',
        'petugas_id',
        'petugas_nama',
        'tanggal_verifikasi',
        'verifikator_id',
        'verifikator_nama',
        'catatan_verifikasi',
        'pimpinan_id',
        'kode_billing',
        'dasar_hukum',
        'is_legacy',
    ];

    protected $casts = [
        'durasi' => 'integer',
        'luas_m2' => 'decimal:2',
        'jumlah_reklame' => 'integer',
        'tarif_pajak_persen' => 'decimal:2',
        'masa_berlaku_mulai' => 'date',
        'masa_berlaku_sampai' => 'date',
        'jatuh_tempo' => 'date',
        'tanggal_buat' => 'datetime',
        'tanggal_verifikasi' => 'datetime',
        'is_legacy' => 'boolean',
    ];

    public function jenisPajak(): BelongsTo
    {
        return $this->belongsTo(JenisPajak::class, 'jenis_pajak_id');
    }

    public function subJenisPajak(): BelongsTo
    {
        return $this->belongsTo(SubJenisPajak::class, 'sub_jenis_pajak_id');
    }

    public function objekRetribusi(): BelongsTo
    {
        return $this->belongsTo(ObjekRetribusiSewaTanah::class, 'objek_retribusi_id');
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

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    public static function generateNomorSkrd(): string
    {
        $tahun = date('Y');
        $bulan = date('m');
        $count = self::whereYear('tanggal_buat', $tahun)
            ->whereMonth('tanggal_buat', $bulan)
            ->count() + 1;
        $number = str_pad($count, 6, '0', STR_PAD_LEFT);

        return "SKRD/{$tahun}/{$bulan}/{$number}";
    }
}
