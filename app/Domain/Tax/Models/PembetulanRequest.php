<?php

namespace App\Domain\Tax\Models;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembetulanRequest extends Model
{
    use SoftDeletes, HasUuids;
    protected $table = 'pembetulan_requests';

    protected $fillable = [
        'tax_id',
        'user_id',
        'alasan',
        'omzet_baru',
        'lampiran',
        'status',
        'catatan_petugas',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'omzet_baru' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
