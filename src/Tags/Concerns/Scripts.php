<?php

namespace MityDigital\FuseUtilities\Tags\Concerns;

use Statamic\Facades\GlobalSet;

trait Scripts
{
    public function scripts()
    {
        $location = $this->params->get('location', null);

        if (! $location || ! in_array($location, ['head', 'body'])) {
            throw new \Exception('Missing "location" parameter for fuse:scripts tag.');
        }

        return $this->scriptsLoad($location);
    }

    protected function scriptsLoad($location)
    {
        $metadata = GlobalSet::findByHandle('metadata')
            ->in($this->context->get('site')->handle);

        // get the environment
        $environment = config('app.env', 'production');

        if (
            ($environment === 'local' && $metadata->get('javascript_local', false)) ||
            ($environment === 'staging' && $metadata->get('javascript_staging', false)) ||
            ($environment === 'production' && $metadata->get('javascript_production', true))
        ) {
            // only process the scripts for the given location
            return collect($metadata->get('javascript', []))
                ->filter(fn ($javascript) => $javascript['location'] === $location && $javascript['enabled'])
                ->map(function ($javascript) {
                    if ($javascript['type'] === 'script_tag') {
                        // make a script tag
                        $script = '<script';

                        if ($javascript['asynchronous']) {
                            $script .= ' async';
                        }

                        if ($javascript['defer']) {
                            $script .= ' defer';
                        }

                        if (isset($javascript['additional_attributes']) && $javascript['additional_attributes']) {
                            $script .= ' ' . $javascript['additional_attributes'];
                        }

                        $script .= ' src="'.$javascript['src'].'"></script>';

                        return $script;
                    }

                    if ($javascript['type'] === 'inline_script') {
                        // output the included javascript inline
                        $script = '<script type="text/javascript">'."\r\n";
                        $script .= $javascript['script']['code'];
                        $script .= "\r\n".'</script>';

                        return $script;
                    }
                })->join("\r\n");
        }
    }
}
