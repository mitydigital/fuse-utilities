<?php

namespace MityDigital\FuseUtilities\Data;

use Spatie\LaravelData\Data;

class FormMessageData extends Data
{
    public function __construct(
        public ?string $icon,
        public string $heading,
        public ?string $content
    ) {}
}
