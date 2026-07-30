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

it('uses native-resolution crops when width is omitted but aspect ratio is provided', function () {
    $image = Image::make(imageAssetMock('jpg', 1200, 800), [
        'aspect_ratio' => '1/1 md:2/1',
        'quality' => '70 md:60',
    ]);

    expect($image->fallback)->toContain('/img/jpg')
        ->and($image->fallback)->toContain('w=1200')
        ->and($image->fallback)->toContain('h=600')
        ->and($image->fallback_srcset)->toBeNull()
        ->and($image->sizes)->toBeNull()
        ->and($image->width)->toBe(1200)
        ->and($image->height)->toBe(600)
        ->and($image->sources)->toHaveCount(2)
        ->and($image->sources[0]->media)->toBe('(min-width: 768px)')
        ->and($image->sources[0]->sizes)->toBeNull()
        ->and($image->sources[0]->srcset['webp'])->toHaveCount(1)
        ->and($image->sources[0]->srcset['webp'][0]['descriptor'])->toBe('')
        ->and($image->sources[0]->srcset['webp'][0]['url'])->toContain('w=1200')
        ->and($image->sources[0]->srcset['webp'][0]['url'])->toContain('h=600')
        ->and($image->sources[0]->srcset['jpg'][0]['url'])->toContain('w=1200')
        ->and($image->sources[1]->media)->toBeNull()
        ->and($image->sources[1]->srcset['webp'][0]['url'])->toContain('w=800')
        ->and($image->sources[1]->srcset['webp'][0]['url'])->toContain('h=800')
        ->and($image->fallback_srcset)->toBeNull();
});

it('uses the untouched native asset when width and aspect ratio are omitted', function () {
    $image = Image::make(imageAssetMock('jpg', 1200, 800));

    expect($image->fallback)->toBe('/assets/example.jpg')
        ->and($image->sources)->toHaveCount(1)
        ->and($image->sources[0]->srcset['webp'][0]['url'])->toContain('fm=webp')
        ->and($image->sources[0]->srcset['webp'][0]['url'])->not->toContain('w=')
        ->and($image->sources[0]->srcset['webp'][0]['url'])->not->toContain('fit=')
        ->and($image->sources[0]->srcset['jpg'][0]['url'])->toBe('/assets/example.jpg')
        ->and($image->fallback_srcset)->toBeNull();
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
