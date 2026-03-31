<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum PaymentChannel: string implements HasLabel, HasColor, HasIcon
{
    case TOKOPEDIA = 'TOKOPEDIA';
    case ALFAMART = 'ALFAMART';
    case INDOMARET = 'INDOMARET';
    case QRISBJATIM = 'QRISBJATIM';
    case BJATIM = 'BJATIM';
    case BNI = 'BNI';
    case MANUAL = 'MANUAL';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TOKOPEDIA => 'Tokopedia',
            self::ALFAMART => 'Alfamart',
            self::INDOMARET => 'Indomaret',
            self::QRISBJATIM => 'QRIS Bank Jatim',
            self::BJATIM => 'Teller/Mobile Bank Jatim',
            self::BNI => 'Bank BNI',
            self::MANUAL => 'Transfer Langsung RKUD',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::TOKOPEDIA => 'success',
            self::ALFAMART => 'danger',
            self::INDOMARET => 'warning',
            self::QRISBJATIM => 'info',
            self::BJATIM => 'info',
            self::BNI => 'primary',
            self::MANUAL => 'gray',
        };
    }

    public function getIcon(): string | BackedEnum | Htmlable | null
    {
        return match ($this) {
            self::TOKOPEDIA, self::ALFAMART, self::INDOMARET => Heroicon::OutlinedShoppingCart,
            self::QRISBJATIM => Heroicon::OutlinedQrCode,
            self::BJATIM, self::BNI => Heroicon::OutlinedBuildingLibrary,
            self::MANUAL => Heroicon::OutlinedBanknotes,
        };
    }
}
