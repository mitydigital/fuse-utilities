<?php

namespace MityDigital\FuseUtilities\Data;

use Spatie\LaravelData\Data;

class ImageSourceData extends Data
{
    /**
     * @param  array<string, array<int, array{url: string, descriptor: string}>>  $srcset
     */
    public function __construct(
        public ?string $media,
        public ?string $sizes,
        public array $srcset,
    ) {}
}
