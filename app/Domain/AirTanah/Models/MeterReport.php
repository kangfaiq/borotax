<?php

namespace App\Domain\AirTanah\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MeterReport extends Model
{
    use SoftDeletes, HasFactory, HasUuids, HasEncryptedAttributes;
    protected $table = 'meter_reports';

    /**
     * Kolom yang dienkripsi
     */
    protected array $encryptedAttributes = [
        'user_nik',
        'user_name',
        'photo_url',
        'latitude',
        'longitude',
    ];

    protected $fillable = [
        'tax_object_id',
        'user_id',
        'user_nik',
        'user_name',
        'meter_reading_before',
        'meter_reading_after',
        'usage',
        'photo_url',
        'latitude',
        'longitude',
        'location_verified',
        'status',
        'reported_at',
        'skpd_id',
    ];

    protected $casts = [
        'meter_reading_before' => 'integer',
        'meter_reading_after' => 'integer',
        'usage' => 'integer',
        'location_verified' => 'boolean',
        'reported_at' => 'datetime',
    ];

    /**
     * Boot method untuk calculate usage
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto calculate usage
            if (isset($model->meter_reading_before) && isset($model->meter_reading_after)) {
                $model->usage = $model->meter_reading_after - $model->meter_reading_before;
            }
        });
    }

    /**
     * Get water object (tax_objects via WaterObject scope)
     */
    public function waterObject(): BelongsTo
    {
        return $this->belongsTo(WaterObject::class, 'tax_object_id');
    }

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get SKPD
     */
    public function skpdAirTanah(): HasOne
    {
        return $this->hasOne(SkpdAirTanah::class, 'meter_report_id');
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk submitted (menunggu proses)
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'submitted' => 'Menunggu Proses',
            'processing' => 'Sedang Diproses',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => $this->status,
        };
    }
}
