<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum TaxAssessmentLetterType: string implements HasLabel, HasColor, HasIcon
{
    case SKPDKB = 'skpdkb';
    case SKPDKBT = 'skpdkbt';
    case SKPDLB = 'skpdlb';
    case SKPDN = 'skpdn';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SKPDKB => 'SKPDKB',
            self::SKPDKBT => 'SKPDKBT',
            self::SKPDLB => 'SKPDLB',
            self::SKPDN => 'SKPDN',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SKPDKB => 'danger',
            self::SKPDKBT => 'warning',
            self::SKPDLB => 'success',
            self::SKPDN => 'gray',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::SKPDKB => Heroicon::OutlinedExclamationTriangle,
            self::SKPDKBT => Heroicon::OutlinedReceiptPercent,
            self::SKPDLB => Heroicon::OutlinedBanknotes,
            self::SKPDN => Heroicon::OutlinedDocumentText,
        };
    }

    public function allowsGeneratedBilling(): bool
    {
        return in_array($this, [self::SKPDKB, self::SKPDKBT], true);
    }

    public function allowsCreditCompensation(): bool
    {
        return $this === self::SKPDLB;
    }

    public function generatedBillingTrailingSuffix(): ?string
    {
        return match ($this) {
            self::SKPDKB, self::SKPDKBT => '19',
            default => null,
        };
    }
}