@props([
    'asset',
    'width' => null,
    'aspectRatio' => null,
    'quality' => null,
    'fit' => 'crop_focal',
    'sizes' => null,
    'alt' => null,
    'class' => null,
    'pictureClass' => null,
    'style' => null,
    'loading' => 'lazy',
    'decoding' => 'async',
])

@php
    $image = \MityDigital\FuseUtilities\Support\Image::make($asset, [
        'width' => $width,
        'aspect_ratio' => $aspectRatio,
        'quality' => $quality,
        'fit' => $fit,
        'sizes' => $sizes,
    ]);

    $imageAlt = $alt ?? $image->alt ?? '';
@endphp

<picture @if ($pictureClass) class="{{ $pictureClass }}" @endif>
    @foreach ($image->sources as $source)
        @foreach ($image->formats as $format => $mimeType)
            @continue(empty($source->srcset[$format]))

            <source
                @if ($source->media) media="{{ $source->media }}" @endif
                type="{{ $mimeType }}"
                srcset="{{ collect($source->srcset[$format])->map(fn ($item) => $item['url'].($item['descriptor'] ? ' '.$item['descriptor'] : ''))->implode(', ') }}"
                @if ($source->sizes) sizes="{{ $source->sizes }}" @endif
            />
        @endforeach
    @endforeach

    <img
        src="{{ $image->fallback }}"
        @if ($image->fallback_srcset) srcset="{{ $image->fallback_srcset }}" sizes="{{ $image->sizes }}" @endif
        alt="{{ $imageAlt }}"
        @if ($loading) loading="{{ $loading }}" @endif
        @if ($decoding) decoding="{{ $decoding }}" @endif
        @if ($image->width) width="{{ $image->width }}" @endif
        @if ($image->height) height="{{ $image->height }}" @endif
        @if ($class) class="{{ $class }}" @endif
        @if ($style) style="{{ $style }}" @endif
    />
</picture>
