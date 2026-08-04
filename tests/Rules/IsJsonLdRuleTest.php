<?php

use Illuminate\Support\Facades\Validator;
use MityDigital\FuseUtilities\Rules\IsJsonLdRule;

function validateJsonLd(mixed $value): Illuminate\Contracts\Validation\Validator
{
    return Validator::make(
        ['json_ld' => $value],
        ['json_ld' => [new IsJsonLdRule]]
    );
}

it('passes with valid JSON-LD as a Statamic code field value', function () {
    $validator = validateJsonLd(json_encode([
        'json_ld' => [
            'code' => json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => 'Jane Doe',
            ]),
            'mode' => 'json',
        ],
    ]));

    expect($validator->passes())->toBeTrue();
});

it('passes with valid JSON-LD as a plain string', function () {
    $validator = validateJsonLd(json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => 'Jane Doe',
    ]));

    expect($validator->passes())->toBeTrue();
});

it('passes when value is blank', function () {
    expect(validateJsonLd(null)->passes())->toBeTrue()
        ->and(validateJsonLd('')->passes())->toBeTrue()
        ->and(validateJsonLd(['code' => null, 'mode' => 'json'])->passes())->toBeTrue();
});

it('fails when JSON is invalid', function () {
    $validator = validateJsonLd([
        'code' => '{"@context": "https://schema.org", "@type": "Person",}',
        'mode' => 'json',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('json_ld'))->toBe('The JSON-LD must be valid JSON.');
});

it('fails when script tags are included', function () {
    $validator = validateJsonLd([
        'code' => '<script type="application/ld+json">{"@context":"https://schema.org"}</script>',
        'mode' => 'json',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('json_ld'))->toBe('Do not include script tags. Only provide raw JSON-LD.');
});

it('detects script tags case-insensitively', function () {
    $validator = validateJsonLd([
        'code' => '<SCRIPT type="application/ld+json">{"@context":"https://schema.org"}</SCRIPT>',
        'mode' => 'json',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('json_ld'))->toBe('Do not include script tags. Only provide raw JSON-LD.');
});
