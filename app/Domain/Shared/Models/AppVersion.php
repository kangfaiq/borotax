<?php

namespace App\Domain\Shared\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'app_versions';

    protected $fillable = [
        'platform',
        'min_version',
        'latest_version',
        'force_update',
        'maintenance_mode',
        'message',
        'store_url',
    ];

    protected $casts = [
        'force_update' => 'boolean',
        'maintenance_mode' => 'boolean',
    ];

    /**
     * Scope untuk platform tertentu
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Get Android version
     */
    public static function getAndroid(): ?self
    {
        return self::where('platform', 'android')->first();
    }

    /**
     * Get iOS version
     */
    public static function getIos(): ?self
    {
        return self::where('platform', 'ios')->first();
    }

    /**
     * Check if app needs update
     */
    public function needsUpdate(string $currentVersion): bool
    {
        return version_compare($currentVersion, $this->min_version, '<');
    }

    /**
     * Check if maintenance mode is active
     */
    public function isUnderMaintenance(): bool
    {
        return $this->maintenance_mode;
    }
}
