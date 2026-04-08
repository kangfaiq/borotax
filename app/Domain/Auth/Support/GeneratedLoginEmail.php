<?php

namespace App\Domain\Auth\Support;

use App\Domain\Auth\Models\User;
use Illuminate\Support\Str;

class GeneratedLoginEmail
{
    public static function isGenerated(?string $email): bool
    {
        return filled($email) && str($email)->lower()->endsWith('@generated.local');
    }

    public static function sourceLabel(?string $email): string
    {
        return self::isGenerated($email) ? 'Username Otomatis' : 'Email WP';
    }

    public static function sourceColor(?string $email): string
    {
        return self::isGenerated($email) ? 'warning' : 'success';
    }

    public static function forWajibPajak(?string $namaLengkap, ?string $alamat, ?string $phoneNumber): string
    {
        $namaSegment = self::buildSegment($namaLengkap, 18, 'wp');
        $alamatSegment = self::buildSegment(
            $alamat,
            18,
            'alamat',
            ['jl', 'jalan', 'no', 'nomor', 'rt', 'rw', 'desa', 'kel', 'kelurahan', 'kecamatan', 'kabupaten', 'kota']
        );
        $phoneSegment = self::buildPhoneSegment($phoneNumber);

        do {
            $randomSegment = self::buildRandomSegment();
            $localPart = self::truncateLocalPart(implode('.', [
                $namaSegment,
                $alamatSegment,
                $phoneSegment,
                $randomSegment,
            ]));
            $email = $localPart . '@generated.local';
        } while (User::where('email_hash', User::generateHash($email))->exists());

        return $email;
    }

    private static function buildSegment(?string $value, int $maxLength, string $fallback, array $stopWords = []): string
    {
        $normalizedValue = (string) Str::of((string) $value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish();

        $parts = collect(explode(' ', $normalizedValue))
            ->filter(fn(string $part): bool => $part !== '' && !ctype_digit($part) && !in_array($part, $stopWords, true))
            ->take(3)
            ->values();

        $segment = $parts->isEmpty() ? $fallback : implode('-', $parts->all());
        $segment = substr($segment, 0, $maxLength);

        return rtrim($segment, '-');
    }

    private static function buildPhoneSegment(?string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phoneNumber) ?? '';

        if ($digits === '') {
            return '0000';
        }

        return substr(str_pad($digits, 4, '0', STR_PAD_LEFT), -4);
    }

    private static function buildRandomSegment(): string
    {
        return str_pad(strtolower(base_convert((string) random_int(0, 1679615), 10, 36)), 4, '0', STR_PAD_LEFT);
    }

    private static function truncateLocalPart(string $localPart): string
    {
        return rtrim(substr($localPart, 0, 64), '.-');
    }
}