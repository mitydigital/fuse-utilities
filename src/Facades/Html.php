<?php

namespace MityDigital\FuseUtilities\Facades;

use Illuminate\Support\Facades\Facade;
use MityDigital\FuseUtilities\Support\Html as HtmlSupport;

class Html extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return HtmlSupport::class;
    }
}
