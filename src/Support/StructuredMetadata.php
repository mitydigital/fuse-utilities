<?php

namespace MityDigital\FuseUtilities\Support;

use Illuminate\Support\Arr;
use Statamic\Fields\Value;
use Statamic\Tags\Glide;
use Statamic\Tags\Nav;

class StructuredMetadata
{
    protected array $data = [];

    public function add(array $data, $image = null): void
    {
        $this->data[] = (object) [
            'code' => $data,
            'image' => $image,
        ];
    }

    public function get(array ...$sources): array
    {
        $sources = array_merge($this->data, ...$sources);
        $output = [];

        foreach ($sources as $source) {
            if ($source instanceof Value) {
                $source = $source->value();
            }

            $output[] = $this->process(
                is_array($source) ? Arr::get($source, 'code') : $source->code,
                is_array($source) ? Arr::get($source, 'image') : $source->image,
            );
        }

        return $output;
    }

    protected function process(array|string $json, $image = null): string
    {
        $width = 1920;
        $height = 1080;

        if (! is_array($json)) {
            $json = json_decode($json, true);
        }

        if (Arr::get($json, '@type') === 'Organization') {
            $width = 1080;
        }

        $image_url = null;
        if ($image) {
            $glide = app(Glide::class);
            $glide->setContext([]);
            $glide->setParameters([
                'absolute' => true,
                'width' => $width,
                'height' => $height,
                'src' => $image,
            ]);
            $image_url = $glide->index();
        }

        $json = json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        if ($image_url) {
            $search = [
                '{{ $image }}',
                '{{ $image}}',
                '{{$image }}',
                '{{$image}}',
                '{{ image }}',
                '{{ image}}',
                '{{image }}',
                '{{image}}',
            ];

            foreach ($search as $s) {
                $json = str_replace($s, $image_url, $json);
            }
        }

        return $json;
    }

    public function breadcrumbs($context)
    {
        $site_defaults = Arr::get($context, 'site_defaults');
        $breadcrumbs = Arr::get($context, 'breadcrumbs');
        $segment_1 = Arr::get($context, 'segment_1');

        if ($site_defaults->breadcrumbs && $breadcrumbs->value() !== false && $segment_1) {
            $nav = app(Nav::class);
            $nav->setContext($context);
            $nav->setParameters([]);

            $breadcrumbs = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => collect($nav->breadcrumbs())
                    ->map(fn ($breadcrumb, $index) => [
                        '@type' => 'ListItem',
                        'position' => $index,
                        'name' => strip_tags($breadcrumb->title),
                        'item' => $breadcrumb->permalink,
                    ])
                    ->toArray(),
            ];

            return json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return false;
    }
}
