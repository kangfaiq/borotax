<?php

namespace App\Domain\Auth\Support;

class PasswordStandards
{
    public static function requirements(): array
    {
        return [
            'Panjang minimal password adalah tujuh (7) karakter.',
            'Terdiri dari minimal satu (1) karakter berupa huruf kapital (A-Z).',
            'Terdiri dari minimal satu (1) karakter berupa huruf kecil (a-z).',
            'Terdiri dari minimal satu (1) karakter berupa angka (0-9).',
            'Terdiri dari minimal satu tanda baca atau karakter non-alphabetic seperti !, @, #, $, %, ^.',
        ];
    }

    public static function rules(bool $confirmed = true): array
    {
        $rules = ['required', 'string', new \App\Domain\Auth\Support\StrongPassword()];

        if ($confirmed) {
            $rules[] = 'confirmed';
        }

        return $rules;
    }
}