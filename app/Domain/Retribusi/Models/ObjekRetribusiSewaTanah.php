<?php

namespace App\Domain\Retribusi\Models;

use App\Domain\Master\Models\JenisPajak;
use App\Domain\Master\Models\SubJenisPajak;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use App\Domain\Tax\Models\TaxObject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObjekRetribusiSewaTanah extends Model
{
    use HasUuids, HasEncryptedAttributes, SoftDeletes;

    protected $table = 'objek_retribusi_sewa_tanah';

    protected array $encryptedAttributes = [
        'nik',
        'nama_pemilik',
        'alamat_pemilik',
        'nama_objek',
        'alamat_objek',
    ];

    protected $fillable = [
        'jenis_pajak_id',
        'sub_jenis_pajak_id',
        'tax_object_id',
        'npwpd',
        'nopd',
        'nik',
        'nik_hash',
        'nama_pemilik',
        'alamat_pemilik',
        'nama_objek',
        'alamat_objek',
        'kecamatan',
        'kelurahan',
        'luas_m2',
        'is_active',
    ];

    protected $casts = [
        'nopd' => 'integer',
        'luas_m2' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! $model->jenis_pajak_id) {
                $model->jenis_pajak_id = JenisPajak::where('kode', '42101')->value('id');
            }

            if (! $model->nopd && $model->npwpd) {
                $existing = static::where('npwpd', $model->npwpd)->max('nopd');
                $model->nopd = ((int) ($existing ?? 0)) + 1;
            }
        });
    }

    public function jenisPajak(): BelongsTo
    {
        return $this->belongsTo(JenisPajak::class, 'jenis_pajak_id');
    }

    public function subJenisPajak(): BelongsTo
    {
        return $this->belongsTo(SubJenisPajak::class, 'sub_jenis_pajak_id');
    }

    public function objekReklame(): BelongsTo
    {
        return $this->belongsTo(TaxObject::class, 'tax_object_id');
    }
}
