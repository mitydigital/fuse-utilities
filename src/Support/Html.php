<?php

namespace MityDigital\FuseUtilities\Support;

class Html
{
    public function toText(string $html): string
    {
        $text = strip_tags(
            preg_replace('/<[^>]+>/', ' ', $html)
        );

        $text = html_entity_decode($text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
