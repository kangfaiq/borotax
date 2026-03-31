<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum TaxAssessmentLetterStatus: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'draft';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Disetujui => 'success',
            self::Ditolak => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => Heroicon::OutlinedClock,
            self::Disetujui => Heroicon::OutlinedCheckCircle,
            self::Ditolak => Heroicon::OutlinedXCircle,
        };
    }
}