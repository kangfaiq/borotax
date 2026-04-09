<?php

namespace App\Domain\Auth\Models;

use App\Domain\Shared\Traits\HasEncryptedAttributes;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Domain\WajibPajak\Models\WajibPajak;
use App\Domain\Tax\Models\Tax;
use App\Domain\Master\Models\Pimpinan;
use App\Domain\AirTanah\Models\MeterReport;
use App\Domain\Reklame\Models\ReklameRequest;
use App\Domain\Gebyar\Models\GebyarSubmission;
use App\Domain\Shared\Models\Notification;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes, HasEncryptedAttributes;

    /**
     * Kolom yang dienkripsi (ditandai 🔐 di database_schema.md)
     * Note: email TIDAK dienkripsi karena diperlukan untuk autentikasi
     */
    protected array $encryptedAttributes = [
        'nik',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_whatsapp',
        'no_telp',
        'foto_ktp_url',
        'foto_selfie_url',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'email_hash',
        'password',
        'pin',
        'active_session_id',
        'active_session_channel',
        'active_session_ip',
        'active_session_user_agent',
        'active_session_started_at',
        'nik',
        'nik_hash',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'province_code',
        'regency_code',
        'district_code',
        'village_code',
        'birth_regency_code',
        'no_whatsapp',
        'no_telp',
        'foto_ktp_url',
        'foto_selfie_url',
        'status',
        'role',
        'navigation_mode',
        'pimpinan_id',
        'total_kupon_undian',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'password_changed_at',
        'must_change_password',
        'verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'pin',
        'remember_token',
        'nik', // Hide encrypted NIK
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'password_changed_at' => 'datetime',
        'active_session_started_at' => 'datetime',
        'must_change_password' => 'boolean',
        'failed_login_attempts' => 'integer',
        'total_kupon_undian' => 'integer',
    ];

    /**
     * Boot method untuk generate hash
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Generate hash untuk email jika ada (email tidak dienkripsi)
            if (isset($model->attributes['email']) && !empty($model->attributes['email'])) {
                $model->email_hash = self::generateHash($model->attributes['email']);
            }
            // Note: NIK hash sekarang di-generate oleh trait HasEncryptedAttributes
            // sebelum enkripsi terjadi, sehingga hash dibuat dari nilai asli
        });
    }

    /**
     * Generate hash deterministic untuk kolom terenkripsi
     */
    public static function generateHash(string $value): string
    {
        return hash('sha256', trim(strtolower($value)));
    }

    /**
     * Cek akses ke Filament Panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Hanya role admin, verifikator, dan petugas yang bisa akses
        return in_array($this->role, ['admin', 'verifikator', 'petugas']);
    }

    /**
     * Get pimpinan (for verifikator)
     */
    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(Pimpinan::class, 'pimpinan_id');
    }

    /**
     * Get wajib pajak
     */
    public function wajibPajak(): HasOne
    {
        return $this->hasOne(WajibPajak::class, 'user_id');
    }

    /**
     * Get taxes
     */
    public function taxes(): HasMany
    {
        return $this->hasMany(Tax::class, 'user_id');
    }

    /**
     * Get meter reports
     */
    public function meterReports(): HasMany
    {
        return $this->hasMany(MeterReport::class, 'user_id');
    }

    /**
     * Get reklame requests
     */
    public function reklameRequests(): HasMany
    {
        return $this->hasMany(ReklameRequest::class, 'user_id');
    }

    /**
     * Get gebyar submissions
     */
    public function gebyarSubmissions(): HasMany
    {
        return $this->hasMany(GebyarSubmission::class, 'user_id');
    }

    /**
     * Get app notifications (renamed from notifications to avoid conflict)
     */
    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Cek apakah akun terkunci
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Lock akun
     */
    public function lockAccount(int $minutes = 15): void
    {
        $this->locked_until = now()->addMinutes($minutes);
        $this->save();
    }

    /**
     * Reset failed login attempts
     */
    public function resetFailedAttempts(): void
    {
        $this->failed_login_attempts = 0;
        $this->locked_until = null;
        $this->save();
    }

    /**
     * Increment failed login attempts
     */
    public function incrementFailedAttempts(): void
    {
        $this->failed_login_attempts++;

        // Lock setelah 5 percobaan gagal
        if ($this->failed_login_attempts >= 5) {
            $this->lockAccount();
        }

        $this->save();
    }

    /**
     * Scope untuk role tertentu
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope untuk admin panel users
     */
    public function scopeAdminUsers($query)
    {
        return $query->whereIn('role', ['admin', 'verifikator', 'petugas']);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is verifikator
     */
    public function isVerifikator(): bool
    {
        return $this->role === 'verifikator';
    }

    /**
     * Check if user is petugas
     */
    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }
    /**
     * Check if user has specific role(s)
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }

        return $this->role === $roles;
    }

    /**
     * Check if user prefers top navigation mode.
     */
    public function usesTopNavigation(): bool
    {
        return $this->navigation_mode !== 'sidebar';
    }
}
