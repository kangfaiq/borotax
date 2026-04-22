<?php

namespace App\Enums;

enum HistoriPajakAccessStatus: string
{
    case SUKSES = 'sukses';
    case GAGAL_CAPTCHA = 'gagal_captcha';
    case GAGAL_NPWPD_TIDAK_DITEMUKAN = 'gagal_npwpd_tidak_ditemukan';
    case GAGAL_FORMAT = 'gagal_format';
    case RATE_LIMITED = 'rate_limited';

    public function label(): string
    {
        return match ($this) {
            self::SUKSES => 'Sukses',
            self::GAGAL_CAPTCHA => 'Captcha Gagal',
            self::GAGAL_NPWPD_TIDAK_DITEMUKAN => 'NPWPD Tidak Ditemukan',
            self::GAGAL_FORMAT => 'Format Tidak Valid',
            self::RATE_LIMITED => 'Rate Limited',
        };
    }
}
