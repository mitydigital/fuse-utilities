<?php

use MityDigital\FuseUtilities\Support\StructuredMetadata;
use Statamic\Tags\Glide;

function decodeMetadata(string $json): array
{
    return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
}

it('returns an empty array when there is no metadata', function () {
    $metadata = new StructuredMetadata;

    expect($metadata->get())->toBe([]);
});

it('returns metadata added via add', function () {
    $metadata = new StructuredMetadata;

    $metadata->add([
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => 'Jane Doe',
    ]);

    $result = $metadata->get();

    expect($result)->toHaveCount(1);

    expect(decodeMetadata($result[0]))->toMatchArray([
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => 'Jane Doe',
    ]);
});

it('accepts multiple source arrays', function () {
    $metadata = new StructuredMetadata;

    $global = [
        [
            'code' => [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => 'Global Org',
            ],
            'image' => null,
        ],
    ];

    $page = [
        [
            'code' => [
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => 'Jane Doe',
            ],
            'image' => null,
        ],
    ];

    $result = $metadata->get($global, $page);

    expect($result)->toHaveCount(2);

    expect(decodeMetadata($result[0]))->toMatchArray([
        '@type' => 'Organization',
        'name' => 'Global Org',
    ]);

    expect(decodeMetadata($result[1]))->toMatchArray([
        '@type' => 'Person',
        'name' => 'Jane Doe',
    ]);
});

it('accepts JSON strings as code values', function () {
    $metadata = new StructuredMetadata;

    $result = $metadata->get([
        [
            'code' => json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => 'Jane Doe',
            ]),
            'image' => null,
        ],
    ]);

    expect(decodeMetadata($result[0]))->toMatchArray([
        '@type' => 'Person',
        'name' => 'Jane Doe',
    ]);
});

it('accepts object-like source items', function () {
    $metadata = new StructuredMetadata;

    $result = $metadata->get([
        (object) [
            'code' => [
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => 'Jane Doe',
            ],
            'image' => null,
        ],
    ]);

    expect($result)->toHaveCount(1);

    expect(decodeMetadata($result[0]))->toMatchArray([
        '@type' => 'Person',
        'name' => 'Jane Doe',
    ]);
});

it('replaces image placeholders with a Glide URL', function () {
    $this->mock(Glide::class, function ($mock) {
        $mock->shouldReceive('setContext')
            ->once()
            ->with([]);

        $mock->shouldReceive('setParameters')
            ->once()
            ->with([
                'absolute' => true,
                'width' => 1920,
                'height' => 1080,
                'src' => 'assets/example.jpg',
            ]);

        $mock->shouldReceive('index')
            ->once()
            ->andReturn('https://example.com/img/assets/example.jpg?w=1920&h=1080');
    });

    $metadata = new StructuredMetadata;

    $result = $metadata->get([
        [
            'code' => [
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => 'Jane Doe',
                'image' => '{{ image }}',
            ],
            'image' => 'assets/example.jpg',
        ],
    ]);

    expect(decodeMetadata($result[0]))->toMatchArray([
        '@type' => 'Person',
        'image' => 'https://example.com/img/assets/example.jpg?w=1920&h=1080',
    ]);
});

it('uses square dimensions for organization images', function () {
    $this->mock(Glide::class, function ($mock) {
        $mock->shouldReceive('setContext')
            ->once()
            ->with([]);

        $mock->shouldReceive('setParameters')
            ->once()
            ->with([
                'absolute' => true,
                'width' => 1080,
                'height' => 1080,
                'src' => 'assets/logo.jpg',
            ]);

        $mock->shouldReceive('index')
            ->once()
            ->andReturn('https://example.com/img/assets/logo.jpg?w=1080&h=1080');
    });

    $metadata = new StructuredMetadata;

    $result = $metadata->get([
        [
            'code' => [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => 'Example Org',
                'logo' => '{{ image }}',
            ],
            'image' => 'assets/logo.jpg',
        ],
    ]);

    expect(decodeMetadata($result[0]))->toMatchArray([
        '@type' => 'Organization',
        'logo' => 'https://example.com/img/assets/logo.jpg?w=1080&h=1080',
    ]);
});
