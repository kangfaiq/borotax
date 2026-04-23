<?php

namespace App\Filament\Forms\Components;

use App\Domain\Shared\Services\DecimalInputNormalizer;
use Filament\Forms\Components\TextInput;

final class FilamentDecimalInput
{
    public static function configure(TextInput $input): TextInput
    {
        return $input
            ->type('text')
            ->extraInputAttributes(['inputmode' => 'decimal'])
            ->rule('numeric')
            ->mutateStateForValidationUsing(fn ($state) => DecimalInputNormalizer::normalizeDecimalString($state))
            ->dehydrateStateUsing(fn ($state) => DecimalInputNormalizer::normalizeDecimalString($state));
    }
}