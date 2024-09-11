<?php

namespace MityDigital\FuseUtilities\Tags\Concerns;

use Statamic\Facades\Image;
use Statamic\Facades\URL;

trait JsonSchema
{
    /**
     * The {{ json_schema }} tag.
     *
     * @return string|array
     */
    public function jsonSchema()
    {
        // get the schema
        $schema = $this->context->get('json_ld');

        if ($schema) {
            // get the schema value
            $schema = $schema->value();

            // get the image
            $image = $this->context->get('image');

            if ($image) {
                // search and replace these
                $search = [
                    '{{ image }}',
                    '{{ image}}',
                    '{{image }}',
                    '{{image}}',
                ];

                // glide the image
                $manipulator = Image::manipulate($image->value());
                $manipulator->setParam('w', '2400');
                $imageUrl = URL::makeAbsolute($manipulator->build());

                // do the replace
                foreach ($search as $s) {
                    $schema = str_replace($s, $imageUrl, $schema);
                }
            }

            return $schema;
        }
    }
}
