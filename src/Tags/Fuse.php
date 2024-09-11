<?php

namespace MityDigital\FuseUtilities\Tags;

use MityDigital\FuseUtilities\Tags\Concerns\Form;
use MityDigital\FuseUtilities\Tags\Concerns\Image;
use MityDigital\FuseUtilities\Tags\Concerns\JsonSchema;
use MityDigital\FuseUtilities\Tags\Concerns\Scripts;
use Statamic\Tags\Tags;

class Fuse extends Tags
{
    use Form;
    use Image;
    use JsonSchema;
    use Scripts;

    protected function getParamOrContext($key, $default = null)
    {
        if ($this->params->has($key)) {
            return $this->params->get($key);
        }

        if ($this->context->has($key)) {
            return $this->context->value($key);
        }

        return $default;
    }
}
