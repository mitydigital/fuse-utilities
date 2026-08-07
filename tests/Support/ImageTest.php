<?php

use MityDigital\FuseUtilities\Support\Image;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Image as ImageAPI;

function imageAssetMock(string $extension = 'jpg', int $width = 2000, int $height = 1000, ?string $alt = 'A blog image'): Asset
{
    $asset = Mockery::mock(Asset::class);
    $asset->shouldReceive('url')->andReturn('/assets/example.'.$extension);
    $asset->shouldReceive('width')->andReturn($width);
    $asset->shouldReceive('height')->andReturn($height);
    $asset->shouldReceive('extension')->andReturn($extension);
    $asset->shouldReceive('mimeType')->andReturn(match ($extension) {
        'png' => 'image/png',
        'webp' => 'image/webp',
        default => 'image/jpeg',
    });
    $asset->shouldReceive('get')->with('alt')->andReturn($alt);
    $asset->shouldReceive('get')->with('focus')->andReturn(null);

    return $asset;
}

beforeEach(function () {
    $this->imageUrlCalls = 0;

    config()->set('fuse-utilities.image.default_width', 350);
    config()->set('fuse-utilities.image.breakpoints', [
        'sm' => 640,
        'md' => 768,
        'lg' => 1024,
    ]);

    ImageAPI::shouldReceive('manipulate')
        ->andReturnUsing(function (Asset $asset, array $params): string {
            test()->imageUrlCalls++;

            return '/img/'.$params['fm'].'?'.http_build_query($params);
        });
});

it('builds breakpoint-aware webp and original format sources', function () {
    $image = Image::make(imageAssetMock(), [
        'width' => '350 md:320 lg:400',
        'aspect_ratio' => '1/1 md:2/1',
        'quality' => '80 md:70',
    ]);

    expect($image->formats)->toBe([
        'webp' => 'image/webp',
        'jpg' => 'image/jpeg',
    ])
        ->and($image->sizes)->toBe('(min-width: 1024px) 400px, (min-width: 768px) 320px, 350px')
        ->and($image->sources)->toHaveCount(3)
        ->and($image->sources[0]->media)->toBe('(min-width: 1024px)')
        ->and($image->sources[0]->sizes)->toBe('400px')
        ->and($image->sources[0]->srcset['webp'][0]['descriptor'])->toBe('400w')
        ->and($image->sources[0]->srcset['webp'][1]['descriptor'])->toBe('800w')
        ->and($image->sources[1]->media)->toBe('(min-width: 768px)')
        ->and($image->sources[1]->sizes)->toBe('320px')
        ->and($image->sources[2]->media)->toBeNull()
        ->and($image->sources[2]->sizes)->toBe('350px')
        ->and($image->sources[2]->srcset['webp'][0]['descriptor'])->toBe('350w')
        ->and($image->sources[2]->srcset['webp'][1]['descriptor'])->toBe('700w');
});

it('uses the configured glide default when quality is omitted', function () {
    $image = Image::make(imageAssetMock(), [
        'width' => '60',
        'aspect_ratio' => '1/1',
    ]);

    expect($image->fallback)->not->toContain('&q=')
        ->and($image->sources)->toHaveCount(1)
        ->and($image->sources[0]->srcset['webp'][0]['url'])->toContain('w=60')
        ->and($image->sources[0]->srcset['webp'][0]['url'])->toContain('h=60')
        ->and($image->sources[0]->srcset['webp'][0]['url'])->toContain('fit=crop')
        ->and($image->sources[0]->srcset['webp'][1]['url'])->toContain('w=120')
        ->and($image->sources[0]->srcset['webp'][1]['url'])->toContain('h=120');
});

it('uses the original image format after webp for png assets', function () {
    $image = Image::make(imageAssetMock('png'), ['width' => '320']);

    expect($image->formats)->toBe([
        'webp' => 'image/webp',
        'png' => 'image/png',
    ])
        ->and($image->sources[0]->srcset['png'][0]['url'])->toContain('/img/png');
});

it('does not manipulate non-raster assets', function () {
    $asset = imageAssetMock('svg', 1200, 800);

    $image = Image::make($asset);

    expect($image->fallback)->toBe('/assets/example.svg')
        ->and($image->fallback_srcset)->toBeNull()
        ->and($image->sources)->toBe([])
        ->and($image->width)->toBe(1200)
        ->and($image->height)->toBe(800);
});

it('uses configured widths when width is omitted', function () {
    $image = Image::make(imageAssetMock('jpg', 2000, 1000));

    expect($image->sizes)->toBe('(min-width: 1024px) 1024px, (min-width: 768px) 768px, (min-width: 640px) 640px, 350px')
        ->and($image->sources)->toHaveCount(4)
        ->and($image->sources[0]->media)->toBe('(min-width: 1024px)')
        ->and($image->sources[0]->srcset['webp'][0]['descriptor'])->toBe('1024w')
        ->and($image->sources[1]->media)->toBe('(min-width: 768px)')
        ->and($image->sources[1]->srcset['webp'][0]['descriptor'])->toBe('768w')
        ->and($image->sources[2]->media)->toBe('(min-width: 640px)')
        ->and($image->sources[2]->srcset['webp'][0]['descriptor'])->toBe('640w')
        ->and($image->sources[3]->media)->toBeNull()
        ->and($image->sources[3]->srcset['webp'][0]['descriptor'])->toBe('350w')
        ->and($image->fallback)->toContain('w=1024');
});

it('derives crop heights from default widths', function () {
    $image = Image::make(imageAssetMock('jpg', 2000, 1000), [
        'aspect_ratio' => '1/1 md:2/1',
    ]);

    expect($image->sources[0]->srcset['webp'][0]['url'])->toContain('w=1024')
        ->and($image->sources[0]->srcset['webp'][0]['url'])->toContain('h=512')
        ->and($image->sources[3]->srcset['webp'][0]['url'])->toContain('w=350')
        ->and($image->sources[3]->srcset['webp'][0]['url'])->toContain('h=350');
});

it('caps configured widths at the asset width', function () {
    config()->set('fuse-utilities.image.breakpoints', [
        'sm' => 640,
        'md' => 768,
        'lg' => 1024,
        'xl' => 1280,
        '2xl' => 1536,
    ]);

    $image = Image::make(imageAssetMock('jpg', 1000, 500));

    expect($image->sizes)->toBe('(min-width: 1024px) 1000px, (min-width: 768px) 768px, (min-width: 640px) 640px, 350px')
        ->and($image->sources)->toHaveCount(4)
        ->and($image->sources[0]->media)->toBe('(min-width: 1024px)')
        ->and($image->sources[0]->srcset['webp'])->toHaveCount(1)
        ->and($image->sources[0]->srcset['webp'][0]['descriptor'])->toBe('1000w')
        ->and($image->fallback)->toContain('w=1000');
});

it('uses the untouched native asset before a responsive width begins', function () {
    $image = Image::make(imageAssetMock('jpg', 1600, 900), [
        'width' => 'md:800',
    ]);

    expect($image->sources)->toHaveCount(2)
        ->and($image->sources[0]->media)->toBe('(min-width: 768px)')
        ->and($image->sources[0]->srcset['webp'][0]['descriptor'])->toBe('800w')
        ->and($image->sources[0]->srcset['webp'][1]['descriptor'])->toBe('1600w')
        ->and($image->sources[1]->media)->toBeNull()
        ->and($image->sources[1]->sizes)->toBeNull()
        ->and($image->sources[1]->srcset['jpg'][0]['url'])->toBe('/assets/example.jpg')
        ->and($image->sources[1]->srcset['webp'][0]['url'])->not->toContain('w=')
        ->and($image->sizes)->toBe('(min-width: 768px) 800px, 800px');
});

it('allows sizes to be overridden for fluid layouts', function () {
    $image = Image::make(imageAssetMock(), [
        'width' => '350 md:320',
        'sizes' => '(min-width: 768px) 33vw, 100vw',
    ]);

    expect($image->sizes)->toBe('(min-width: 768px) 33vw, 100vw')
        ->and($image->sources[0]->sizes)->toBe('(min-width: 768px) 33vw, 100vw')
        ->and($image->sources[1]->sizes)->toBe('(min-width: 768px) 33vw, 100vw');
});

it('caches identical glide urls while building the dto', function () {
    Image::make(imageAssetMock(), [
        'width' => '320 md:320',
        'aspect_ratio' => '1/1',
    ]);

    expect($this->imageUrlCalls)->toBe(4);
});
