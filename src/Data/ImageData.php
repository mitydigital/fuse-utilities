<?php

namespace MityDigital\FuseUtilities\Data;

use Spatie\LaravelData\Data;

class ImageData extends Data
{
    /**
     * @param  array<string, string>  $formats
     * @param  array<int, ImageSourceData>  $sources
     */
    public function __construct(
        public string $fallback,
        public ?string $fallback_srcset,
        public ?string $sizes,
        public ?string $alt,
        public ?int $width,
        public ?int $height,
        public array $formats,
        public array $sources,
    ) {}
}
