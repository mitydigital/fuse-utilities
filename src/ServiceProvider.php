<?php

namespace MityDigital\FuseUtilities;

use MityDigital\FuseUtilities\Console\Commands\GenerateTailwindCommand;
use MityDigital\FuseUtilities\Listeners\ImagesWithoutAltListener;
use MityDigital\FuseUtilities\Listeners\PreventDeletingMountsListener;
use MityDigital\FuseUtilities\Tags\Fuse;
use MityDigital\FuseUtilities\Widgets\ImagesWithoutAlt;
use Statamic\Events\EntryDeleting;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        GenerateTailwindCommand::class,
    ];

    protected $listen = [
        EntryDeleting::class => [
            PreventDeletingMountsListener::class,
        ],
    ];

    protected $subscribe = [
        ImagesWithoutAltListener::class,
    ];

    protected $tags = [
        Fuse::class,
    ];

    protected $widgets = [
        ImagesWithoutAlt::class,
    ];

    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../resources/fieldsets' => resource_path('fieldsets/vendor/fuse-utilities'),
        ], 'fuse-utilities-fieldsets');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/fuse-utilities'),
        ], 'fuse-utilities-views');
    }
}
