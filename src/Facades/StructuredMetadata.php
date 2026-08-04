<?php

namespace MityDigital\FuseUtilities\Facades;

use Illuminate\Support\Facades\Facade;
use MityDigital\FuseUtilities\Support\StructuredMetadata as StructuredMetadataSupport;

class StructuredMetadata extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return StructuredMetadataSupport::class;
    }
}
