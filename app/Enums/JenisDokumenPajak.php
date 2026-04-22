<?php

namespace App\Enums;

enum JenisDokumenPajak: string
{
    case BILLING = 'billing';
    case STPD_MANUAL = 'stpd_manual';
    case SURAT_KETETAPAN = 'surat_ketetapan';
    case SKPD_REKLAME = 'skpd_reklame';
    case SKPD_AIR_TANAH = 'skpd_air_tanah';
    case SKRD_SEWA_TANAH = 'skrd_sewa_tanah';

    public function label(): string
    {
        return match ($this) {
            self::BILLING => 'Billing',
            self::STPD_MANUAL => 'STPD Manual',
            self::SURAT_KETETAPAN => 'Surat Ketetapan',
            self::SKPD_REKLAME => 'SKPD Reklame',
            self::SKPD_AIR_TANAH => 'SKPD Air Tanah',
            self::SKRD_SEWA_TANAH => 'SKRD Sewa Tanah',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::BILLING => 'blue',
            self::STPD_MANUAL => 'red',
            self::SURAT_KETETAPAN => 'purple',
            self::SKPD_REKLAME => 'amber',
            self::SKPD_AIR_TANAH => 'cyan',
            self::SKRD_SEWA_TANAH => 'emerald',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::BILLING => 1,
            self::SURAT_KETETAPAN => 2,
            self::STPD_MANUAL => 3,
            self::SKPD_REKLAME => 4,
            self::SKPD_AIR_TANAH => 5,
            self::SKRD_SEWA_TANAH => 6,
        };
    }
}
