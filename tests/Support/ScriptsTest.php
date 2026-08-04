<?php

use Illuminate\Support\Fluent;
use MityDigital\FuseUtilities\Support\Scripts;

function scriptsContext(array $siteDefaults = [], mixed $pageScripts = null): array
{
    return [
        'site_defaults' => new Fluent($siteDefaults),
        'javascripts' => $pageScripts,
    ];
}

it('throws if location is invalid', function () {
    (new Scripts)->get('footer', scriptsContext());
})->throws(Exception::class, 'Location "footer" for Scripts not valid: must be either "head", "body-end" or "body-start".');

it('throws if location is empty', function () {
    (new Scripts)->get('', scriptsContext());
})->throws(Exception::class, 'Missing "location" parameter for Scripts.');

it('returns production scripts by default', function () {
    config()->set('app.env', 'production');

    $result = (new Scripts)->get('head', scriptsContext([
        'javascripts' => [
            [
                'location' => 'head',
                'environment' => [
                    'follow' => 'default',
                ],
                'enabled' => true,
                'code' => '<script src="/global-head.js"></script>',
            ],
        ],
    ]));

    expect($result)->toBe('<script src="/global-head.js"></script>');
});

it('does not return scripts when disabled for the current environment', function () {
    config()->set('app.env', 'local');

    $result = (new Scripts)->get('head', scriptsContext([
        'javascript_local' => false,
        'javascripts' => [
            [
                'location' => 'head',
                'environment' => [
                    'follow' => 'default',
                ],
                'enabled' => true,
                'script' => '<script src="/local-head.js"></script>',
            ],
        ],
    ]));

    expect($result)->toBeNull();
});

it('returns scripts when enabled for local', function () {
    config()->set('app.env', 'local');

    $result = (new Scripts)->get('head', scriptsContext([
        'javascript_local' => true,
        'javascripts' => [
            [
                'location' => 'head',
                'environment' => [
                    'follow' => 'default',
                ],
                'enabled' => true,
                'code' => '<script src="/local-head.js"></script>',
            ],
        ],
    ]));

    expect($result)->toBe('<script src="/local-head.js"></script>');
});

it('filters scripts by location', function () {
    config()->set('app.env', 'production');

    $result = (new Scripts)->get('body-end', scriptsContext([
        'javascripts' => [
            [
                'location' => 'head',
                'environment' => [
                    'follow' => 'default',
                ],
                'enabled' => true,
                'code' => '<script src="/head.js"></script>',
            ],
            [
                'location' => 'body-end',
                'environment' => [
                    'follow' => 'default',
                ],
                'enabled' => true,
                'code' => '<script src="/body.js"></script>',
            ],
        ],
    ]));

    expect($result)->toBe('<script src="/body.js"></script>');
});

it('filters out disabled scripts', function () {
    config()->set('app.env', 'production');

    $result = (new Scripts)->get('head', scriptsContext([
        'javascripts' => [
            [
                'location' => 'head',
                'environment' => [
                    'follow' => 'default',
                ],
                'enabled' => false,
                'code' => '<script src="/disabled.js"></script>',
            ],
            [
                'location' => 'head',
                'environment' => [
                    'follow' => 'default',
                ],
                'enabled' => true,
                'code' => '<script src="/enabled.js"></script>',
            ],
        ],
    ]));

    expect($result)->toBe('<script src="/enabled.js"></script>');
});

it('merges global and page scripts', function () {
    config()->set('app.env', 'production');

    $pageScripts = new class
    {
        public function raw(): array
        {
            return [
                [
                    'location' => 'head',
                    'environment' => [
                        'follow' => 'default',
                    ],
                    'enabled' => true,
                    'code' => '<script src="/page-head.js"></script>',
                ],
            ];
        }
    };

    $result = (new Scripts)->get('head', scriptsContext([
        'javascripts' => [
            [
                'location' => 'head',
                'environment' => [
                    'follow' => 'default',
                ],
                'enabled' => true,
                'code' => '<script src="/global-head.js"></script>',
            ],
        ],
    ], $pageScripts));

    expect($result)->toBe("<script src=\"/global-head.js\"></script>\r\n<script src=\"/page-head.js\"></script>");
});

it('returns an empty string when scripts are enabled but none match', function () {
    config()->set('app.env', 'production');

    $result = (new Scripts)->get('head', scriptsContext([
        'javascripts' => [
            [
                'location' => 'body',
                'environment' => [
                    'follow' => 'default',
                ],
                'enabled' => true,
                'code' => '<script src="/body.js"></script>',
            ],
        ],
    ]));

    expect($result)->toBe(null);
});

it('returns scripts with custom environment enabled for the current environment', function () {
    config()->set('app.env', 'local');

    $result = (new Scripts)->get('head', scriptsContext([
        'javascript_local' => false,
        'javascripts' => [
            [
                'location' => 'head',
                'environment' => [
                    'follow' => 'custom',
                    'enabled_on_local' => true,
                    'enabled_on_staging' => false,
                    'enabled_on_production' => false,
                ],
                'enabled' => true,
                'code' => '<script src="/custom-local.js"></script>',
            ],
        ],
    ]));

    expect($result)->toBe('<script src="/custom-local.js"></script>');
});

it('does not return scripts with custom environment disabled for the current environment', function () {
    config()->set('app.env', 'staging');

    $result = (new Scripts)->get('head', scriptsContext([
        'javascript_staging' => true,
        'javascripts' => [
            [
                'location' => 'head',
                'environment' => [
                    'follow' => 'custom',
                    'enabled_on_local' => true,
                    'enabled_on_staging' => false,
                    'enabled_on_production' => true,
                ],
                'enabled' => true,
                'code' => '<script src="/custom-staging.js"></script>',
            ],
        ],
    ]));

    expect($result)->toBeNull();
});
