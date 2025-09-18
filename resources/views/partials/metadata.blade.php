{{-- Page title and description --}}
@php
    $separator = $metadata->title_separator->value() ?? '|';
    $separator = ' ' . $separator . ' ';
    $site_name = $metadata->site_name ?? config('app.name');

    $change = collect($metadata->change_page_title)
        ->first(fn($config) => $config->collection->handle() === $page->collection->handle());

    // all start here
    $_title = $meta_title->value() ?? ($title->value() ?? $site->name);

    if ($change) {
        $_title = match($change->manipulate_title->value()) {
            'collection_title' => $_title . $separator . $collection->title,
            'custom_text' => $_title . $separator . $change->custom_text,
            'replace_title' => $change->custom_text,
            default => $_title
        };
    }

    if ($meta_include_title_suffix->raw() === 'include')
    {
        $_title .= $separator.' ' .$site_name;
    }
@endphp

<title>{{ $_title }}</title>
@if ($meta_description->value())
    <meta content="{{ $meta_description }}" name="description">
@endif
<link href="{{ $site->url }}" rel="home">

{{-- Basic OG details --}}
<meta content="website" property="og:type">
@if ($meta_title->value() || $title->value())
    <meta content="{{ $meta_title->value() ?? $title->value() }}" property="og:title">
@endif
@if ($meta_description)
    <meta content="{{ $meta_description }}" property="og:description">
@endif
<meta content="{{ $permalink }}" property="og:url">
<meta content="{{ $site->locale }}" property="og:locale">

{{-- Social Sharing (OG) Image --}}
@if ($og_image->value())
    <s:glide :src="$og_image" width="1200" height="630" fit="crop_focal" absolute="true">
        <meta content="{{ $url }}" property="og:image">
        @if (isset($alt) && $alt)
            <meta content="{{ $alt }}" property="og:image:alt">
        @else
            <meta content="{{ $meta_title->value() ?? ($title->value() ?? $site->name->value()) }}" property="og:image:alt">
        @endif
    </s:glide>
@else
    <s:glide :src="$metadata->og_image" width="1200" height="630" fit="crop_focal" absolute="true">
        <meta content="{{ $url }}" property="og:image">
    </s:glide>
    <meta content="{{ $meta_title->value() ?? ($title->value() ?? $site->name->value()) }}" property="og:image:alt">
@endif
<meta content="image/jpeg" property="og:image:type">
<meta content="1200" property="og:image:width">
<meta content="630" property="og:image:height">

{{-- No index and no follow --}}
@if(($environment === 'local' && !$metadata->noindex_local) ||
    ($environment === 'staging' && !$metadata->noindex_staging) ||
    ($environment === 'production' && !$metadata->noindex_production))

    @if ($noindex->raw() && $nofollow->raw())
        <meta name="robots" content="noindex, nofollow">
    @elseif($noindex->raw())
        <meta name="robots" content="nofollow">
    @elseif($nofollow->raw())
        <meta name="robots" content="noindex">
    @endif

@else
    <meta name="robots" content="noindex, nofollow">
@endif

{{-- Canonical URL --}}
@if ($canonical_type->raw() === 'external')
    <link href="{{ $canonical_url }}" rel="canonical"/>
@elseif($canonical_type->raw() === 'internal')
    <link href="{{ $canonical_entry->permalink }}" rel="canonical"/>
@else
    <link href="{{ $permalink }}" rel="canonical"/>
@endif