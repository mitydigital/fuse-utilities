<?php

namespace MityDigital\FuseUtilities\Support;

use MityDigital\FuseUtilities\Contracts\ImageUrlGenerator;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Image as ImageAPI;

class StatamicImageUrlGenerator implements ImageUrlGenerator
{
    /**
     * @param  array<string, scalar>  $params
     */
    public function generate(Asset $asset, array $params = []): string
    {
        if ($params === []) {
            return $asset->url() ?? '';
        }

        return ImageAPI::manipulate($asset, $params);
    }
}
