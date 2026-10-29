<?php

namespace MityDigital\FuseUtilities\Support;

use MityDigital\FuseUtilities\Facades\Html;

class PageBuilder
{
    public function toText(array $pageBuilder, string $view = 'fuse.page_builder'): string
    {
        $html = $this->toHtml($pageBuilder, $view);

        return Html::toText($html);
    }

    public function toHtml(array $pageBuilder, string $view = 'fuse.page_builder'): string
    {
        return view($view, [
            'page_builder' => $pageBuilder,
        ])->render();
    }
}
