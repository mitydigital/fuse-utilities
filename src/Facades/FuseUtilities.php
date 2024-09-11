<?php

namespace MityDigital\FuseUtilities\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getImagesMissingAltCache()
 * @method static bool isCaptchaEnabled(?string $site, ?string $environment, ?string $form)
 *
 * @see \MityDigital\FuseUtilities\Support\FuseUtilities
 */
class FuseUtilities extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \MityDigital\FuseUtilities\Support\FuseUtilities::class;
    }
}
