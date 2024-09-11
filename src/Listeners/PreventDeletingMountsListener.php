<?php

namespace MityDigital\FuseUtilities\Listeners;

use Statamic\Events\EntryDeleting;
use Statamic\Facades\Collection;

class PreventDeletingMountsListener
{
    /**
     * Inspired by Peak (thanks!)
     * https://github.com/studio1902/statamic-peak/blob/main/app/Listeners/PreventDeletingMounts.php
     */
    public function handle(EntryDeleting $event): void
    {
        if (app()->isProduction() && Collection::findByMount($event->entry)) {
            throw new \Exception(trans('strings.collection_mounted', ['title' => $event->entry['title']]));
        }
    }
}
