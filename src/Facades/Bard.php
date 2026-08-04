<?php

namespace MityDigital\FuseUtilities\Facades;

use Illuminate\Support\Facades\Facade;
use MityDigital\FuseUtilities\Support\Bard as BardSupport;

class Bard extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BardSupport::class;
    }
}
