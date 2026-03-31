<?php

namespace App\Domain\Simpadu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model untuk tabel dat_objek_pajak di database simpadu
 * Read-only - data dari sistem existing
 */
class DatObjekPajak extends Model
{
    protected $connection = 'simpadu';
    protected $table = 'dat_objek_pajak';
    protected $primaryKey = 'NOP';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'NOP',
        'NAME',
        'JALAN_OP',
        'STATUS',
    ];

    /**
     * Get SPTPD AT records for this objek
     */
    public function sptpdAt(): HasMany
    {
        return $this->hasMany(DatSptpdAt::class, 'nop', 'NOP');
    }

    /**
     * Scope untuk objek aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('STATUS', 1);
    }

    /**
     * Scope untuk search by nama
     */
    public function scopeSearchByNama($query, string $nama)
    {
        return $query->where('NAME', 'like', '%' . $nama . '%');
    }

    /**
     * Get meter terakhir dari dat_sptpd_at
     */
    public function getMeterTerakhirAttribute()
    {
        $lastSptpd = $this->sptpdAt()
            ->orderBy('tgldata', 'desc')
            ->orderBy('masa_awal', 'desc')
            ->first();

        return $lastSptpd?->hariini;
    }

    /**
     * Get tanggal lapor terakhir
     */
    public function getTanggalLaporTerakhirAttribute()
    {
        $lastSptpd = $this->sptpdAt()
            ->orderBy('tgldata', 'desc')
            ->first();

        return $lastSptpd?->tgldata;
    }

    /**
     * Get masa pajak terakhir
     */
    public function getMasaPajakTerakhirAttribute()
    {
        $lastSptpd = $this->sptpdAt()
            ->orderBy('masa_awal', 'desc')
            ->first();

        return $lastSptpd?->masa_awal;
    }
}
