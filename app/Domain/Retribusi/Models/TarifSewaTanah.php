<?php

namespace App\Domain\Retribusi\Models;

use App\Domain\Master\Models\SubJenisPajak;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TarifSewaTanah extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tarif_sewa_tanah';

    protected $fillable = [
        'sub_jenis_pajak_id',
        'tarif_nominal',
        'satuan_waktu',
        'berlaku_mulai',
        'berlaku_sampai',
        'is_active',
    ];

    protected $casts = [
        'tarif_nominal' => 'decimal:2',
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
        'is_active' => 'boolean',
    ];

    public function subJenisPajak(): BelongsTo
    {
        return $this->belongsTo(SubJenisPajak::class, 'sub_jenis_pajak_id');
    }

    public static function lookupTarif(string $subJenisPajakId, ?string $tanggalReferensi = null): ?self
    {
        $tanggal = $tanggalReferensi ?? now()->toDateString();

        return static::where('sub_jenis_pajak_id', $subJenisPajakId)
            ->where('is_active', true)
            ->where('berlaku_mulai', '<=', $tanggal)
            ->where(fn ($q) => $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', $tanggal))
            ->orderByDesc('berlaku_mulai')
            ->first();
    }
}
