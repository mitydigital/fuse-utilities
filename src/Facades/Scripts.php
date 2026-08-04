<?php

namespace MityDigital\FuseUtilities\Facades;

use Illuminate\Support\Facades\Facade;
use MityDigital\FuseUtilities\Support\Scripts as ScriptsSupport;

class Scripts extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ScriptsSupport::class;
    }
}
