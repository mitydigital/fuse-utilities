<?php

namespace MityDigital\FuseUtilities\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Cache;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetSaving;
use Statamic\Events\AssetUploaded;

class ImagesWithoutAltListener
{
    public function handleSaving(AssetSaving $event)
    {
        if ($event->asset->isDirty('alt')) {
            Cache::forget(FuseUtilities::getImagesMissingAltCache());
        }
    }

    public function handleChange(AssetDeleted|AssetUploaded $event)
    {
        Cache::forget(FuseUtilities::getImagesMissingAltCache());
    }

    public function subscribe(Dispatcher $events)
    {
        if (! collect(config('statamic.cp.widgets', []))->contains('type', 'images_missing_alt')) {
            return;
        }

        // only clear if the alt is dirty
        $events->listen(AssetSaving::class, [ImagesMissingAltListener::class, 'handleSaving']);

        // simply clear the cache
        $events->listen(AssetDeleted::class, [ImagesMissingAltListener::class, 'handleChange']);
        $events->listen(AssetUploaded::class, [ImagesMissingAltListener::class, 'handleChange']);
    }
}
