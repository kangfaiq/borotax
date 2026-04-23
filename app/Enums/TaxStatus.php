<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum TaxStatus: string implements HasLabel, HasColor, HasIcon
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Verified = 'verified';
    case Expired = 'expired';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case PartiallyPaid = 'partially_paid';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Paid => 'Lunas',
            self::Verified => 'Terverifikasi',
            self::PartiallyPaid => 'Dibayar Sebagian',
            self::Expired => 'Lewat Jatuh Tempo',
            self::Rejected => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Verified => 'info',
            self::PartiallyPaid => 'info',
            self::Expired => 'gray',
            self::Rejected => 'danger',
            self::Cancelled => 'gray',
        };
    }

    public function getIcon(): string | BackedEnum | Htmlable | null
    {
        return match ($this) {
            self::Pending => Heroicon::OutlinedClock,
            self::Paid => Heroicon::OutlinedCheckCircle,
            self::Verified => Heroicon::OutlinedShieldCheck,
            self::PartiallyPaid => Heroicon::OutlinedReceiptPercent,
            self::Expired => Heroicon::OutlinedExclamationTriangle,
            self::Rejected => Heroicon::OutlinedXCircle,
            self::Cancelled => Heroicon::OutlinedNoSymbol,
        };
    }

    public static function activeStatuses(): array
    {
        return [
            self::Pending->value,
            self::Paid->value,
            self::Verified->value,
            self::Expired->value,
            self::PartiallyPaid->value,
        ];
    }

    public static function payableStatuses(): array
    {
        return [
            self::Pending->value,
            self::Verified->value,
            self::Expired->value,
            self::PartiallyPaid->value,
        ];
    }
}
