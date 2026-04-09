<?php

namespace App\Domain\Auth\Support;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $password = (string) $value;

        if (mb_strlen($password) < 7) {
            $fail('Password harus minimal 7 karakter.');
        }

        if (! preg_match('/[A-Z]/', $password)) {
            $fail('Password harus mengandung minimal satu huruf kapital (A-Z).');
        }

        if (! preg_match('/[a-z]/', $password)) {
            $fail('Password harus mengandung minimal satu huruf kecil (a-z).');
        }

        if (! preg_match('/[0-9]/', $password)) {
            $fail('Password harus mengandung minimal satu angka (0-9).');
        }

        if (! preg_match('/[^A-Za-z0-9]/', $password)) {
            $fail('Password harus mengandung minimal satu tanda baca atau karakter non-alphabetic seperti !, @, #, $, %, ^.');
        }
    }
}