<?php

namespace MityDigital\FuseUtilities\Support;

use Illuminate\Support\Arr;
use Statamic\Entries\Entry;

class Scripts
{
    protected array $scripts = [
        'head' => [],
        'body-end' => [],
        'body-start' => [],
    ];

    public function get(string $location, array $context): ?string
    {
        if (! $location || ! in_array($location, ['head', 'body-end', 'body-start'])) {
            if (! $location) {
                throw new \Exception('Missing "location" parameter for Scripts.');
            } else {
                throw new \Exception(sprintf('Location "%s" for Scripts not valid: must be either "head", "body-end" or "body-start".', $location));
            }
        }

        $environment = config('app.env', 'production');
        $site_defaults = Arr::get($context, 'site_defaults');

        $scripts = collect();

        $enabledForEnvironment =
            ($environment === 'local' && $site_defaults->get('javascript_local', false)) ||
            ($environment === 'staging' && $site_defaults->get('javascript_staging', false)) ||
            ($environment === 'production' && $site_defaults->get('javascript_production', true));

        // site settings
        $scripts = collect(array_merge(
            $site_defaults->get('javascripts', []),
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
     * Add a Script from a "Script" entry (see Scripts collection)
     */
    public function add(Entry $script): void
    {
        $this->scripts[$script->get('location')][$script->id()] = $script->get('code');
    }
}
