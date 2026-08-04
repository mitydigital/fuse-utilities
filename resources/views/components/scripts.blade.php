@blaze
@props([
    'context' => [],
    'location',
])
{!! \MityDigital\FuseUtilities\Facades\Scripts::get($location, $context) !!}
