<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum InstansiKategori: string implements HasLabel, HasColor, HasIcon
{
    case Opd = 'opd';
    case Instansi = 'instansi';
    case Lembaga = 'lembaga';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Opd => 'OPD',
            self::Instansi => 'Instansi',
            self::Lembaga => 'Lembaga',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Opd => 'primary',
            self::Instansi => 'info',
            self::Lembaga => 'success',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Opd => Heroicon::OutlinedBuildingOffice2,
            self::Instansi => Heroicon::OutlinedBuildingLibrary,
            self::Lembaga => Heroicon::OutlinedAcademicCap,
        };
    }
}