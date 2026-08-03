<?php

namespace MityDigital\FuseUtilities\Support;

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
}
