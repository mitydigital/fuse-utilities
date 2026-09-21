<?php

namespace MityDigital\FuseUtilities\Contracts;

use Statamic\Contracts\Assets\Asset;

interface ImageUrlGenerator
{
    /**
     * Generate a URL for an asset, optionally with Statamic Glide manipulation parameters.
     *
     * An empty parameter array represents the original, unmanipulated asset.
     *
     * @param  array<string, scalar>  $params
     */
    public function generate(Asset $asset, array $params = []): string;
}
