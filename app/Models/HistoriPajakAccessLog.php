<?php

namespace App\Models;

use App\Enums\HistoriPajakAccessStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string|null $npwpd
 * @property int|null $tahun
 * @property string|null $ip
 * @property string|null $user_agent
 * @property HistoriPajakAccessStatus $status
 * @property int|null $jumlah_dokumen
 * @property \Carbon\CarbonInterface $created_at
 */
class HistoriPajakAccessLog extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'npwpd',
        'tahun',
        'ip',
        'user_agent',
        'status',
        'jumlah_dokumen',
        'created_at',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'jumlah_dokumen' => 'integer',
        'status' => HistoriPajakAccessStatus::class,
        'created_at' => 'datetime',
    ];
}
