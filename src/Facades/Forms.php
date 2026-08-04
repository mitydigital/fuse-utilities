<?php

namespace MityDigital\FuseUtilities\Facades;

use Illuminate\Support\Facades\Facade;
use MityDigital\FuseUtilities\Support\Forms as FormsSupport;

class Forms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FormsSupport::class;
    }
}
