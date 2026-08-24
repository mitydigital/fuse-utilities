<?php

namespace MityDigital\FuseUtilities\SearchTransformers;

use MityDigital\FuseUtilities\Facades\PageBuilder;

class PageBuilderTransformer
{
    public function handle($value, $field, $searchable)
    {
        return PageBuilder::toText($searchable->{$field} ?? []);
    }
}
