<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TaxAssessmentReason: string implements HasLabel
{
    case Pemeriksaan = 'pemeriksaan';
    case JabatanTidakSampaikanSptpd = 'jabatan_tidak_sampaikan_sptpd';
    case JabatanTidakKooperatif = 'jabatan_tidak_kooperatif';
    case DataBaru = 'data_baru';
    case LebihBayar = 'lebih_bayar';
    case Nihil = 'nihil';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pemeriksaan => 'Hasil Pemeriksaan',
            self::JabatanTidakSampaikanSptpd => 'Secara Jabatan: Tidak Menyampaikan SPTPD',
            self::JabatanTidakKooperatif => 'Secara Jabatan: Tidak Kooperatif Saat Pemeriksaan',
            self::DataBaru => 'Data Baru / Tambahan',
            self::LebihBayar => 'Lebih Bayar',
            self::Nihil => 'Nihil',
        };
    }
}