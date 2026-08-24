<?php

namespace MityDigital\FuseUtilities\Facades;

use Illuminate\Support\Facades\Facade;
use MityDigital\FuseUtilities\Support\PageBuilder as PageBuilderSupport;

class PageBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PageBuilderSupport::class;
    }
}
