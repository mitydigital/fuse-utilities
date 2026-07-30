<?php

namespace MityDigital\FuseUtilities;

use Illuminate\Support\Facades\Blade;
use MityDigital\FuseUtilities\Console\Commands\GenerateTailwindCommand;
use MityDigital\FuseUtilities\Fieldtypes\SettingsFeatures;
use MityDigital\FuseUtilities\Listeners\ImagesWithoutAltListener;
use MityDigital\FuseUtilities\Listeners\PreventDeletingMountsListener;
use Statamic\Events\EntryDeleting;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        GenerateTailwindCommand::class,
    ];

    protected $fieldtypes = [
        SettingsFeatures::class,
    ];

    protected $listen = [
        EntryDeleting::class => [
            PreventDeletingMountsListener::class,
        ],
    ];

    protected $subscribe = [
        ImagesWithoutAltListener::class,
    ];

    protected $vite = [
        'input' => [
            'resources/js/cp.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/fuse-utilities/'),
        ], 'fuse-utilities-assets');

        $this->publishes([
            __DIR__.'/../resources/fieldsets' => resource_path('fieldsets/vendor/fuse-utilities'),
        ], 'fuse-utilities-fieldsets');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/fuse-utilities'),
        ], 'fuse-utilities-views');

        $this->bootBlade();
    }

    public function bootBlade()
    {
        Blade::directive('comment', function (?string $expression): string {
            if (blank($expression)) {
                return "<?php if (config('fuse-utilities.blade.render_comments')): ?>";
            }

            return "<?php if (config('fuse-utilities.blade.render_comments')) {
                echo '<!-- '.str_replace('--', '—', e((string) {$expression})).' -->';
            } ?>";
        });

        Blade::directive('endcomment', function (): string {
            return '<?php endif; ?>';
        });
    }
}
