<?php

namespace MityDigital\FuseUtilities\SearchTransformers;

use MityDigital\FuseUtilities\Facades\Bard;

class BardTransformer
{
    public function handle($value, $field, $searchable)
    {
        if ($searchable->{$field}) {
            return Bard::render($searchable->{$field} ?? [], text: true);
        }

        return null;
    }
}
