<?php

namespace App\Domain\Shared\Services;

final class DecimalInputNormalizer
{
    public static function normalizeDecimalString(null|string|int|float $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        $normalized = preg_replace('/\s+/u', '', $normalized) ?? $normalized;

        $hasComma = str_contains($normalized, ',');
        $hasDot = str_contains($normalized, '.');

        if ($hasComma && $hasDot) {
            if (strrpos($normalized, ',') > strrpos($normalized, '.')) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($hasComma) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return $normalized;
    }

    public static function toFloat(null|string|int|float $value): ?float
    {
        $normalized = self::normalizeDecimalString($value);

        if ($normalized === null || ! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }
}