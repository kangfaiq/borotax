<?php

namespace App\Domain\Simpadu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk tabel dat_sptpd_at di database simpadu
 * Read-only - data dari sistem existing
 */
class DatSptpdAt extends Model
{
    protected $connection = 'simpadu';
    protected $table = 'dat_sptpd_at';
    public $timestamps = false;

    protected $fillable = [
        'nop',
        'hariini',
        'masa_awal',
        'tgldata',
    ];

    protected $casts = [
        'hariini' => 'decimal:2',
        'masa_awal' => 'date',
        'tgldata' => 'date',
    ];

    /**
     * Get objek pajak
     */
    public function objekPajak(): BelongsTo
    {
        return $this->belongsTo(DatObjekPajak::class, 'nop', 'NOP');
    }

    /**
     * Scope untuk NOP tertentu
     */
    public function scopeForNop($query, string $nop)
    {
        return $query->where('nop', $nop);
    }

    /**
     * Scope untuk data terakhir
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('tgldata', 'desc')
            ->orderBy('masa_awal', 'desc');
    }
}
