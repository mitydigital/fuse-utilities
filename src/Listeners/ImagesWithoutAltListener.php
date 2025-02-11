<?php

namespace MityDigital\FuseUtilities\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Cache;
use MityDigital\FuseUtilities\Facades\FuseUtilities;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetSaving;
use Statamic\Events\AssetUploaded;

class ImagesWithoutAltListener
{
    public function handleSaving(AssetSaving $event)
    {
        if (! $this->hasImagesWithoutAltWidget()) {
            return;
        }

        if ($event->asset->isDirty('alt')) {
            Cache::forget(FuseUtilities::getImagesMissingAltCache());
        }
    }

    public function handleChange(AssetDeleted|AssetUploaded $event)
    {
        if (! $this->hasImagesWithoutAltWidget()) {
            return;
        }

        Cache::forget(FuseUtilities::getImagesMissingAltCache());
    }

    protected function hasImagesWithoutAltWidget()
    {
        return collect(config('statamic.cp.widgets', []))->contains('type', 'images_without_alt');
    }

    public function subscribe(Dispatcher $events)
    {
        if (! $this->hasImagesWithoutAltWidget()) {
            return;
        }

        // only clear if the alt is dirty
        $events->listen(AssetSaving::class, [ImagesWithoutAltListener::class, 'handleSaving']);

        // simply clear the cache
        $events->listen(AssetDeleted::class, [ImagesWithoutAltListener::class, 'handleChange']);
        $events->listen(AssetUploaded::class, [ImagesWithoutAltListener::class, 'handleChange']);
    }
}
