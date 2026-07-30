<?php

namespace MityDigital\FuseUtilities\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;

class IsTailwindLikeRule implements ValidationRule
{
    public function __construct(public string $mode = 'integer')
    {
        if (! in_array($this->mode, ['integer', 'ratio'], true)) {
            throw new InvalidArgumentException("Unsupported Tailwind-like validation mode [{$this->mode}].");
        }
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value)) {
            $fail('The :attribute must contain valid Tailwind-like values.');

            return;
        }

        $tokens = preg_split('/\s+/', trim($value), -1, PREG_SPLIT_NO_EMPTY);

        if ($tokens === false || $tokens === []) {
            return;
        }

        foreach ($tokens as $token) {
            if (! $this->isValidToken($token)) {
                $fail('The :attribute must contain valid Tailwind-like values.');

                return;
            }
        }
    }

    protected function isValidToken(string $token): bool
    {
        if (preg_match('/^(?:[^\s:]+:)?([^\s:]+)$/', $token, $matches) !== 1) {
            return false;
        }

        return $this->mode === 'integer'
            ? preg_match('/^\d+$/', $matches[1]) === 1
            : $this->isValidRatio($matches[1]);
    }

    protected function isValidRatio(string $value): bool
    {
        $number = '(?:\d+(?:\.\d+)?|\.\d+)';

        if (preg_match("/^{$number}\/{$number}$/", $value) !== 1) {
            return false;
        }

        [$numerator, $denominator] = explode('/', $value, 2);

        return (float) $numerator > 0 && (float) $denominator > 0;
    }
}
