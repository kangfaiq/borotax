<?php

use App\Domain\Shared\Services\DecimalInputNormalizer;

it('normalizes decimal strings from dot and comma input', function (string $input, string $expected) {
    expect(DecimalInputNormalizer::normalizeDecimalString($input))->toBe($expected);
})->with([
    ['12.5', '12.5'],
    ['12,5', '12.5'],
    ['1.234,56', '1234.56'],
    ['1234,56', '1234.56'],
    ['1,234.56', '1234.56'],
]);

it('converts normalized decimal strings to float values', function (string $input, ?float $expected) {
    expect(DecimalInputNormalizer::toFloat($input))->toBe($expected);
})->with([
    ['12.5', 12.5],
    ['12,5', 12.5],
    ['', null],
]);