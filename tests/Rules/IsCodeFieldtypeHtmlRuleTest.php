<?php

use Illuminate\Support\Facades\Validator;
use MityDigital\FuseUtilities\Rules\IsCodeFieldtypeHtml;

function validateCodeFieldtypeHtml(mixed $value): Illuminate\Contracts\Validation\Validator
{
    return Validator::make(
        ['content' => $value],
        ['content' => [new IsCodeFieldtypeHtml]]
    );
}

it('passes with a code fieldtype payload containing a single HTML element', function () {
    $validator = validateCodeFieldtypeHtml([
        'code' => '<div>Content</div>',
        'mode' => 'htmlmixed',
    ]);

    expect($validator->passes())->toBeTrue();
});

it('passes with a code fieldtype payload containing a script tag', function () {
    $validator = validateCodeFieldtypeHtml([
        'code' => '<script>alert("hello");</script>',
        'mode' => 'htmlmixed',
    ]);

    expect($validator->passes())->toBeTrue();
});

it('passes with a code fieldtype payload containing multiple HTML elements', function () {
    $validator = validateCodeFieldtypeHtml([
        'code' => '<p>First</p><p>Second</p>',
        'mode' => 'htmlmixed',
    ]);

    expect($validator->passes())->toBeTrue();
});

it('passes with an html comment', function () {
    $validator = validateCodeFieldtypeHtml([
        'code' => '<!-- blah -->',
        'mode' => 'htmlmixed',
    ]);

    expect($validator->passes())->toBeTrue();
});

it('passes when the code fieldtype value is blank', function () {
    expect(validateCodeFieldtypeHtml(null)->passes())->toBeTrue()
        ->and(validateCodeFieldtypeHtml('')->passes())->toBeTrue()
        ->and(validateCodeFieldtypeHtml([
            'code' => '',
            'mode' => 'htmlmixed',
        ])->passes())->toBeTrue()
        ->and(validateCodeFieldtypeHtml([
            'code' => null,
            'mode' => 'htmlmixed',
        ])->passes())->toBeTrue();
});

it('fails when the code fieldtype content is plain text', function () {
    $validator = validateCodeFieldtypeHtml([
        'code' => 'Just plain text',
        'mode' => 'htmlmixed',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('content'))->toBe('The content must contain HTML.');
});

it('fails when the code fieldtype content contains no HTML elements', function () {
    $validator = validateCodeFieldtypeHtml([
        'code' => '1 < 2',
        'mode' => 'htmlmixed',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('content'))->toBe('The content must contain HTML.');
});

it('fails when the html fragment is malformed', function () {
    $validator = validateCodeFieldtypeHtml([
        'code' => <<<'HTML'
<div
  class="trustpilot-widget"
  data-locale="en-AU"
  data-template-id="53aa8912dec7e10d38f59f36"
  data-businessunit-id="5d9ae129d29a050001eb01ca"
  data-style-height="140px"
  data-style-width="100%"
  data-theme="light"
  data-stars="4,5"
  data-review-languages="en"
>
  <a href="https://au.trustpilot.com/review/superloop.com" target="_blank" rel="noopener noreferrer">Trustpilot
</div>
HTML,
        'mode' => 'htmlmixed',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('content'))->toBe('The content must contain HTML.');
});

it('fails when a raw text tag contains another tag-like sequence before it closes', function () {
    $validator = validateCodeFieldtypeHtml([
        'code' => <<<'HTML'
<script type="text/javascript">
  window.__productReviewSettings = { brandId: 'f2c581ea-45d3-43e8-84dd-f19a9321460b' };

<script src="https://cdn.productreview.com.au/assets/widgets/loader.js" async></script>
HTML,
        'mode' => 'htmlmixed',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('content'))->toBe('The content must contain HTML.');
});
