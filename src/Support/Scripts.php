<?php

namespace MityDigital\FuseUtilities\Support;

use Illuminate\Support\Arr;
use Livewire\Livewire;
use Statamic\Entries\Entry;

class Scripts
{
    /**
     * Prefix for the keys of scripts handed to Livewire, so they cannot clash with Livewire's own asset keys.
     */
    public const LIVEWIRE_ASSET_KEY_PREFIX = 'fuse-scripts:';

    /**
     * Scripts added during the request, keyed by location then by script key.
     *
     * @var array<string, array<string, string>>
     */
    protected array $scripts = [
        'head' => [],
        'body-end' => [],
        'body-start' => [],
    ];

    /**
     * Keys of scripts handed to Livewire as assets. Livewire outputs them, so the layout must not.
     *
     * @var array<string, true>
     */
    protected array $livewireKeys = [];

    public function get(string $location, array $context): ?string
    {
        $this->validateLocation($location);

        $environment = config('app.env', 'production');
        $site_defaults = Arr::get($context, 'site_defaults');

        $scripts = collect();

        $enabledForEnvironment =
            ($environment === 'local' && $site_defaults?->get('javascript_local', false)) ||
            ($environment === 'staging' && $site_defaults?->get('javascript_staging', false)) ||
            ($environment === 'production' && $site_defaults?->get('javascript_production', true));

        // site settings
        $scripts = collect(array_merge(
            $site_defaults?->get('javascripts', []) ?? [],
            Arr::get($context, 'javascripts')?->raw() ?? []
        ))
            ->filter(function (array $javascript) use ($enabledForEnvironment, $environment, $location) {
                if ($javascript['location'] !== $location) {
                    // location does not match
                    return false;
                }

                if (! $javascript['enabled']) {
                    // is not enabled
                    return false;
                }

                if (Arr::get($javascript, 'environment.follow', 'default') === 'custom') {
                    // script level override
                    if (Arr::get($javascript, 'environment.enabled_on_'.$environment, false)) {
                        return true;
                    }
                } elseif ($enabledForEnvironment) {
                    // enabled for environment
                    return true;
                }

                return false;
            })
            ->values()
            ->map(fn (array $javascript) => $javascript['code']);

        // manually added scripts
        foreach ($this->scripts[$location] as $script) {
            $scripts->add($script);
        }

        if ($scripts->count()) {
            return $scripts->join("\r\n");
        }

        return null;
    }

    /**
     * Add a script for this request: either a "Script" entry (see the Scripts collection), or a string of HTML such as
     * '<script src="..."></script>'.
     *
     * An entry uses its own "location" and its id as the key. A string goes to $location (default "body-end") and its
     * key defaults to a hash of the code. A key is only output once, however many times it is added.
     *
     * When a Livewire component is rendering, the script is loaded as a Livewire asset instead, so it also reaches the
     * page when the component renders in a Livewire update (lazy and deferred components, islands, and components a
     * parent adds). Livewire loads assets once per page, in the <head>.
     */
    public function add(Entry|string $script, ?string $location = null, ?string $key = null): void
    {
        if ($script instanceof Entry) {
            $location = $script->get('location');
            $key = (string) $script->id();
            $code = (string) $script->get('code');

            // an entry without a valid location has never been output, and must not start throwing
            if (! in_array($location, ['head', 'body-end', 'body-start'], true)) {
                return;
            }
        } else {
            $location ??= 'body-end';
            $key ??= md5($script);
            $code = $script;

            $this->validateLocation($location);
        }

        if (isset($this->livewireKeys[$key])) {
            return;
        }

        if ($this->addToLivewire($key, $code)) {
            $this->livewireKeys[$key] = true;

            foreach (array_keys($this->scripts) as $scriptLocation) {
                unset($this->scripts[$scriptLocation][$key]);
            }

            return;
        }

        $this->scripts[$location][$key] = $code;
    }

    /**
     * Hand the script to the Livewire component that is rendering, the way Livewire's @assets directive does. Returns
     * false when Livewire is not installed or no component is rendering.
     */
    protected function addToLivewire(string $key, string $code): bool
    {
        if (! class_exists(Livewire::class) || ! function_exists('Livewire\store')) {
            return false;
        }

        $component = Livewire::current();

        if (! $component) {
            return false;
        }

        \Livewire\store($component)->push('assets', $code, self::LIVEWIRE_ASSET_KEY_PREFIX.$key);

        return true;
    }

    protected function validateLocation(?string $location): void
    {
        if (! $location) {
            throw new \Exception('Missing "location" parameter for Scripts.');
        }

        if (! in_array($location, ['head', 'body-end', 'body-start'])) {
            throw new \Exception(sprintf('Location "%s" for Scripts not valid: must be either "head", "body-end" or "body-start".', $location));
        }
    }
}
