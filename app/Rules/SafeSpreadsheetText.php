<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeSpreadsheetText implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && preg_match('/^[\x00-\x20]*[=+\-@]/u', $value)) {
            $fail('Kolom :attribute tidak boleh berisi formula spreadsheet.');
        }
    }
}
