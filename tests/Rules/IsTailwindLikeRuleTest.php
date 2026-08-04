<?php

use Illuminate\Support\Facades\Validator;
use MityDigital\FuseUtilities\Rules\IsTailwindLikeRule;

function validateTailwindLike(mixed $value, string $mode): Illuminate\Contracts\Validation\Validator
{
    return Validator::make(
        ['value' => $value],
        ['value' => [new IsTailwindLikeRule($mode)]]
    );
}

it('passes with valid integer values', function (string $value) {
    expect(validateTailwindLike($value, 'integer')->passes())->toBeTrue();
})->with([
    'single value' => '250',
    'responsive values' => '250 md:300 lg:250',
    'zero' => '0',
    'arbitrary prefix' => 'tablet:640',
]);

it('fails with invalid integer values', function (string $value) {
    expect(validateTailwindLike($value, 'integer')->fails())->toBeTrue();
})->with([
    'decimal' => '250.5',
    'negative' => '-250',
    'missing value' => 'md:',
    'missing prefix value' => ':250',
    'multiple separators' => 'md:250:300',
    'ratio' => '1/1',
]);

it('passes with valid ratio values', function (string $value) {
    expect(validateTailwindLike($value, 'ratio')->passes())->toBeTrue();
})->with([
    'integer ratio' => '1/1',
    'decimal numerator' => '1.85/1',
    'decimal denominator' => '1/2.2',
    'responsive ratios' => '1.85/1 md:3/2 lg:1/1',
    'leading decimal' => '.5/1',
]);

it('fails with invalid ratio values', function (string $value) {
    expect(validateTailwindLike($value, 'ratio')->fails())->toBeTrue();
})->with([
    'missing numerator' => '/1',
    'missing denominator' => '1/',
    'zero numerator' => '0/1',
    'zero denominator' => '1/0',
    'multiple separators' => '1/2/3',
    'integer value' => '250',
    'missing prefix value' => 'md:',
]);

it('passes with blank values', function () {
    expect(validateTailwindLike(null, 'integer')->passes())->toBeTrue()
        ->and(validateTailwindLike('', 'ratio')->passes())->toBeTrue()
        ->and(validateTailwindLike('   ', 'integer')->passes())->toBeTrue();
});

it('fails with non-string values', function () {
    expect(validateTailwindLike(['250'], 'integer')->fails())->toBeTrue();
});

it('fails with an unknown mode', function () {
    expect(fn () => new IsTailwindLikeRule('unknown'))
        ->toThrow(InvalidArgumentException::class);
});
