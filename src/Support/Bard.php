<?php

namespace MityDigital\FuseUtilities\Support;

use MityDigital\FuseUtilities\Facades\Html;
use Statamic\Facades\Antlers;
use Statamic\Facades\Cascade;
use Statamic\Facades\Site;
use Statamic\Modifiers\CoreModifiers;

class Bard
{
    public function toHtml(mixed $content, bool $antlers = true, ?array $context = null): string
    {
        if (! $content) {
            return '';
        }

        $html = app(CoreModifiers::class)->bardHtml($content);

        if ($antlers) {
            $html = Antlers::parse(
                $html,
                Cascade::instance()
                    ->withContent($context ?? [])
                    ->withSite(Site::current())
                    ->hydrate()
                    ->toArray()
            );
        }

        return html_entity_decode($html);
    }

    public function render(mixed $content, string $view = 'fuse.bard', bool $text = false)
    {
        $render = view($view, [
            'content' => $content,
        ])->render();

        if (! $text) {
            return $render;
        }

        return Html::toText($render);
    }
}
