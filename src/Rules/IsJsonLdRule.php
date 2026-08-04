<?php

namespace MityDigital\FuseUtilities\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsJsonLdRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $code = is_array($value)
            ? ($value['code'] ?? null)
            : $value;

        if (blank($code)) {
            return;
        }

        $value = trim((string) $code);

        if (str_contains(strtolower($code), '<script')) {
            $fail('Do not include script tags. Only provide raw JSON-LD.');

            return;
        }

        if (! json_validate($code)) {
            $fail('The JSON-LD must be valid JSON.');
        }
    }
}
