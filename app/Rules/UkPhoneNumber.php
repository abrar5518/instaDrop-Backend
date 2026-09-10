<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UkPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = preg_replace('/[\s().-]+/', '', (string) $value);

        if (!preg_match('/^(?:\+44\d{10}|0044\d{10}|0\d{10})$/', $normalized)) {
            $fail('The :attribute must be a valid UK phone number, for example 07123 456789 or +44 7123 456789.');
        }
    }

    public static function normalize(string $value): string
    {
        $number = preg_replace('/[\s().-]+/', '', $value);
        if (str_starts_with($number, '0044')) {
            return '+44' . substr($number, 4);
        }
        if (str_starts_with($number, '0')) {
            return '+44' . substr($number, 1);
        }
        return $number;
    }
}
