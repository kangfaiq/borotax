<?php

namespace App\Domain\Auth\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    use HasUuids;

    protected $fillable = [
        'identifier',
        'identifier_hash',
        'code',
        'code_hash',
        'type',
        'attempts',
        'max_attempts',
        'expires_at',
        'is_used',
        'sent_at',
        'verified_at',
        'verification_token',
        'token_expires_at',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'verified_at' => 'datetime',
        'token_expires_at' => 'datetime',
        'is_used' => 'boolean',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
    ];

    /**
     * Generate hash SHA-256 untuk identifier/code
     */
    public static function generateHash(string $value): string
    {
        return hash('sha256', strtolower(trim($value)));
    }

    /**
     * Generate kode OTP 6 digit angka
     */
    public static function generateOtpCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate verification token
     */
    public static function generateVerificationToken(): string
    {
        return str()->random(64);
    }

    /**
     * Buat OTP baru untuk registrasi (email only)
     */
    public static function createForRegistration(
        string $email,
        ?string $ipAddress = null
    ): self {
        $code = self::generateOtpCode();

        return self::create([
            'identifier' => $email,
            'identifier_hash' => self::generateHash($email),
            'code' => $code,
            'code_hash' => self::generateHash($code),
            'type' => 'registration',
            'attempts' => 0,
            'max_attempts' => 3,
            'expires_at' => now()->addSeconds(30),
            'is_used' => false,
            'sent_at' => now(),
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Cek apakah OTP sudah kedaluwarsa
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Cek apakah sudah melebihi max attempts
     */
    public function hasExceededMaxAttempts(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    /**
     * Verifikasi kode OTP
     */
    public function verifyCode(string $code): bool
    {
        return $this->code_hash === self::generateHash($code);
    }

    /**
     * Tandai OTP sebagai sudah digunakan & generate verification token
     */
    public function markAsVerified(): string
    {
        $token = self::generateVerificationToken();

        $this->update([
            'is_used' => true,
            'verified_at' => now(),
            'verification_token' => $token,
            'token_expires_at' => now()->addMinutes(15),
        ]);

        return $token;
    }

    /**
     * Increment attempts
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Cek apakah ada request OTP terlalu sering (cooldown 2 menit)
     */
    public static function hasCooldown(string $identifierHash, string $type = 'registration'): bool
    {
        return self::where('identifier_hash', $identifierHash)
            ->where('type', $type)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();
    }

    /**
     * Hitung jumlah request OTP dalam 15 menit terakhir (max 3x resend)
     */
    public static function countRecentRequests(string $identifierHash, string $type = 'registration'): int
    {
        return self::where('identifier_hash', $identifierHash)
            ->where('type', $type)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();
    }

    /**
     * Cari OTP yang valid berdasarkan verification token
     */
    public static function findByVerificationToken(string $token): ?self
    {
        return self::where('verification_token', $token)
            ->where('is_used', true)
            ->where('token_expires_at', '>=', now())
            ->first();
    }

    /**
     * Mask email: bu**@example.com
     */
    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        $masked = substr($name, 0, 2) . str_repeat('*', max(strlen($name) - 2, 2));
        return $masked . '@' . $domain;
    }
}
