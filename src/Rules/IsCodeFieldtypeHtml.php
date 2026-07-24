<?php

namespace MityDigital\FuseUtilities\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsCodeFieldtypeHtml implements ValidationRule
{
    /**
     * @var array<string, true>
     */
    protected array $voidElements = [
        'area' => true,
        'base' => true,
        'br' => true,
        'col' => true,
        'embed' => true,
        'hr' => true,
        'img' => true,
        'input' => true,
        'link' => true,
        'meta' => true,
        'param' => true,
        'source' => true,
        'track' => true,
        'wbr' => true,
    ];

    /**
     * @var array<string, true>
     */
    protected array $rawTextElements = [
        'script' => true,
        'style' => true,
        'textarea' => true,
        'title' => true,
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $code = $this->extractCodeValue($value);

        if (blank($code)) {
            return;
        }

        if (! is_scalar($code) && ! $code instanceof \Stringable) {
            $fail('The :attribute must contain HTML.');

            return;
        }

        $html = trim((string) $code);

        if ($html === '') {
            return;
        }

        if ($this->containsHtmlFragment($html)) {
            return;
        }

        $fail('The :attribute must contain HTML.');
    }

    protected function extractCodeValue(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        return $value['code'] ?? null;
    }

    protected function containsHtmlFragment(string $value): bool
    {
        $length = strlen($value);
        $stack = [];
        $hasMarkup = false;

        for ($offset = 0; $offset < $length; $offset++) {
            if ($value[$offset] !== '<') {
                continue;
            }

            if (! isset($value[$offset + 1]) || ! $this->isPotentialTagStart($value[$offset + 1])) {
                continue;
            }

            if (str_starts_with(substr($value, $offset), '<!--')) {
                $commentEndOffset = strpos($value, '-->', $offset + 4);

                if ($commentEndOffset === false) {
                    return false;
                }

                $hasMarkup = true;
                $offset = $commentEndOffset + 2;

                continue;
            }

            $closingOffset = $this->findTagEnd($value, $offset);

            if ($closingOffset === null) {
                return false;
            }

            $tag = trim(substr($value, $offset + 1, $closingOffset - $offset - 1));

            if ($tag === '' || str_starts_with($tag, '!') || str_starts_with($tag, '?')) {
                $offset = $closingOffset;

                continue;
            }

            if (str_starts_with($tag, '/')) {
                $tagName = strtolower(trim(substr($tag, 1)));
                $tagName = strtok($tagName, " \t\n\r\f>/") ?: '';

                if ($tagName === '' || $stack === []) {
                    return false;
                }

                $lastTag = array_pop($stack);

                if ($lastTag !== $tagName) {
                    return false;
                }

                $offset = $closingOffset;

                continue;
            }

            [$tagName, $isSelfClosing] = $this->parseOpeningTag($tag);

            if ($tagName === '') {
                return false;
            }

            $hasMarkup = true;

            if (isset($this->rawTextElements[$tagName])) {
                $rawTextClosingTag = '</'.$tagName.'>';
                $rawTextClosingOffset = stripos($value, $rawTextClosingTag, $closingOffset + 1);

                if ($rawTextClosingOffset === false) {
                    return false;
                }

                if ($this->containsTagLikeSequence(substr($value, $closingOffset + 1, $rawTextClosingOffset - $closingOffset - 1))) {
                    return false;
                }

                if (! $isSelfClosing) {
                    $stack[] = $tagName;
                    $offset = $rawTextClosingOffset - 1;

                    continue;
                }
            }

            if (! $isSelfClosing && ! isset($this->voidElements[$tagName])) {
                $stack[] = $tagName;
            }

            $offset = $closingOffset;
        }

        return $hasMarkup && $stack === [];
    }

    /**
     * @return array{0: string, 1: bool}
     */
    protected function parseOpeningTag(string $tag): array
    {
        $isSelfClosing = str_ends_with(rtrim($tag), '/');
        $tagName = strtolower(strtok($tag, " \t\n\r\f>/") ?: '');

        return [$tagName, $isSelfClosing];
    }

    protected function findTagEnd(string $value, int $offset): ?int
    {
        $length = strlen($value);
        $quote = null;

        for ($index = $offset + 1; $index < $length; $index++) {
            $character = $value[$index];

            if ($quote !== null) {
                if ($character === $quote) {
                    $quote = null;
                }

                continue;
            }

            if ($character === '"' || $character === "'") {
                $quote = $character;

                continue;
            }

            if ($character === '>') {
                return $index;
            }
        }

        return null;
    }

    protected function isPotentialTagStart(string $character): bool
    {
        return ctype_alpha($character)
            || $character === '/'
            || $character === '!'
            || $character === '?';
    }

    protected function containsTagLikeSequence(string $value): bool
    {
        $length = strlen($value);

        for ($offset = 0; $offset < $length - 1; $offset++) {
            if ($value[$offset] !== '<') {
                continue;
            }

            if ($this->isPotentialTagStart($value[$offset + 1])) {
                return true;
            }
        }

        return false;
    }
}
