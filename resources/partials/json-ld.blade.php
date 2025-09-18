{{-- Schemas --}}
@php
    $schemas = [];
    foreach($metadata->json_ld as $json_ld) {
        $width = 1920;
        $height = 1080;

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => match($json_ld->schema->value()) {
                'professional_service' => 'ProfessionalService',
                'person' => 'Person',
                'organization' => 'Organization',
                default=> ''
            },
            'name' => $json_ld->name,
            'url' => $homepage,
        ];
        
        if ($schema['@type'] === 'Organization')
        {
            $width = 1080;
        }

        $image_url = null;
        if ($json_ld->image) {
            $glide = app(\Statamic\Tags\Glide::class);
            $glide->setContext([]);
            $glide->setParameters([
                'absolute' => true,
                'width' => $width,
                'height' => $height,
                'src' => $json_ld->image,
            ]);
            $image_url = $glide->index();
            $schema['logo'] = $image_url;
        }

        if ($json_ld->schema->value() ==='custom')
        {
            $schema = $json_ld->json_ld->value();

            if ($image_url) {
                $search = [
                    '{{ image }}',
                    '{{ image}}',
                    '{{image }}',
                    '{{image}}',
                ];

                foreach ($search as $s) {
                    $schema = str_replace($s, $image_url, $schema);
                }
            }
        }

        $schemas[]= $schema;
    }

@endphp
@if (count($schemas))
    <script type="application/ld+json">
        @foreach($schemas as $schema)
            @if (is_array($schema))
                {!! json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
            @else
                {!! $schema !!}
            @endif
        @endforeach
    </script>
@endif

{{-- Breadcrumbs JSON-ld --}}
@if ($metadata->breadcrumbs && isset($segment_1))
    @php
        $nav = app(\Statamic\Tags\Nav::class);
        $nav->setContext($__data);
        $nav->setParameters([]);

        $breadcrumbs = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => collect($nav->breadcrumbs())->map(fn($breadcrumb, $index) => [
                    '@type' => 'ListItem',
                    'position' => $index,
                    'name' => strip_tags($breadcrumb->title),
                    'item' => $breadcrumb->permalink,
                ])->toArray(),
        ];

    @endphp
    <script type="application/ld+json">
        {!! json_encode($breadcrumbs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endif
