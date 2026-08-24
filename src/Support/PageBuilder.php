<?php

namespace MityDigital\FuseUtilities\Support;

class PageBuilder
{
    public function toText(array $pageBuilder, string $view = 'fuse.page_builder'): string
    {
        $html = $this->toHtml($pageBuilder, $view);

        $text = strip_tags(
            preg_replace('/<[^>]+>/', ' ', $html)
        );

        $text = html_entity_decode($text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    public function toHtml(array $pageBuilder, string $view = 'fuse.page_builder'): string
    {
        return view($view, [
            'page_builder' => $pageBuilder,
        ])->render();
    }
}
