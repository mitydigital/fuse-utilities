<?php

namespace MityDigital\FuseUtilities\Tags\Concerns;

use Statamic\Facades\Asset as AssetAPI;
use Statamic\Support\Str;

trait Image
{
    protected array $breakpoints = [
        ['width' => 640, 'breakpoint' => 'sm'],
        ['width' => 768, 'breakpoint' => 'md'],
        ['width' => 1024, 'breakpoint' => 'lg'],
        ['width' => 1280, 'breakpoint' => 'xl'],
        ['width' => 1440, 'breakpoint' => '2xl'],
        ['width' => 1536, 'breakpoint' => '3xl'],
        ['width' => 1680, 'breakpoint' => '4xl'],
        ['width' => 1800, 'breakpoint' => '5xl'],
    ];

    public function image()
    {
        if (! $asset = $this->params->get(['url', 'src'])) {
            return null;
        }

        // load the asset
        $asset = AssetAPI::find($asset);

        $data = [
            ...$asset->toAugmentedArray(),
            'formats' => [
                'webp' => 'image/webp',
                'jpg' => 'image/jpeg',
            ],
            'image' => $asset,
            'process' => true,
            'sizes' => null,
            'srcset' => [],
        ];

        //
        // SIZE
        // (min-width: 768px) 50vw, 100vw
        //
        $sizes = trim($this->getParamOrContext('sizes', ''));
        // if we have a comma or no spaces, then treat it normally
        if (Str::contains($sizes, ',') || ! Str::contains($sizes, ' ')) {
            if ($sizes !== '') {
                $data['sizes'] = $sizes;
            }
        } else {
            // opinionated tailwind-esque stuff
            $sizes = collect(explode(' ', $sizes))
                ->reverse()
                ->map(function (string $size) {
                    $size = Str::trim($size);

                    if (Str::contains($size, ':')) {
                        $tailwind = explode(':', $size)[0];
                        $width = explode(':', $size)[1];

                        // translate tailwind to pixels
                        $breakpoint = collect($this->breakpoints)->first(fn ($breakpoint) => $breakpoint['breakpoint']
                            === $tailwind);

                        if (! $breakpoint) {
                            return null; // not found, so exlude
                        }

                        return sprintf('(min-width: %spx) %s',
                            $breakpoint['width'],
                            $width
                        );

                        return $pixels;
                    }

                    return $size;
                })
                ->filter()
                ->values();

            if ($sizes->count()) {
                $data['sizes'] = $sizes->join(', ');
            }
        }

        //
        // PROCESS?
        //
        $unprocessable = [
            'image/webp',
            'image/gif',
            'image/svg+xml',
        ];

        if (in_array($asset->mime_type, $unprocessable)) {
            $data['process'] = false;
        }

        //
        // QUALITIES
        //
        $quality = collect(explode(' ', trim($this->getParamOrContext('quality'))))
            ->mapWithKeys(function (string $percent) {
                $percent = Str::trim($percent);
                $prefix = 'base';
                if (Str::contains($percent, ':')) {
                    $prefix = explode(':', $percent)[0];
                    $percent = explode(':', $percent)[1];
                }

                return [
                    $prefix => $percent,
                ];
            })
            ->filter();

        if (! $quality->has('base')) {
            $quality->put('base', 85); // set the default quality
        }

        //
        // ASPECT RATIO
        //
        $aspectRatios = collect(explode(' ', trim($this->getParamOrContext('aspect_ratio'))))
            ->mapWithKeys(function (string $ratio) {
                if (Str::substrCount($ratio, '/') === 1) {
                    $ratio = Str::trim($ratio);
                    $prefix = 'base';
                    if (Str::contains($ratio, ':')) {
                        $prefix = explode(':', $ratio)[0];
                        $ratio = explode(':', $ratio)[1];
                    }

                    $aspect = explode('/', $ratio);

                    return [
                        $prefix => $aspect[1] / $aspect[0],
                    ];
                }

                return [
                    'error' => null,
                ];
            })
            ->filter();

        if (! $aspectRatios->has('base')) {
            $aspectRatios->put('base', $asset->height / $asset->width); // set the default (original)
        }

        if ($aspectRatios->count() > 1) {
            $data['original_ratio'] = $data['ratio'];
        } else {
            // use the provided aspect
            $data['original_ratio'] = $aspectRatios->first();
        }
        $data['ratio'] = $aspectRatios->toArray();

        //
        // SRCSET
        //
        $max = $this->getParamOrContext('max', '1440');
        $min = $this->getParamOrContext('min', '320');

        $widths = [
            320,
            480,
            640,
            768,
            1024,
            1280,
            1440,
            1536,
            1680,
            1800,
            2000,
            2400,
            3000,
        ];

        $srcset = [];
        foreach ($widths as $width) {
            // only process if width is less than or equal to max and min
            if ($width >= $min && $width <= $max) {
                $ratio = [
                    'width' => $width,
                    'height' => null,
                    'ratio' => $this->getResponsiveImageProperty($width, $aspectRatios),
                    'quality' => $this->getResponsiveImageProperty($width, $quality),
                ];

                $ratio['height'] = $ratio['width'] * $ratio['ratio'];

                $srcset[] = $ratio;
            }
        }

        $data['srcset'] = $srcset;

        return $data;
    }

    protected function getResponsiveImageProperty($width, $data)
    {
        // get the index of the breakpoint
        $index = null;
        foreach ($this->breakpoints as $idx => $point) {
            if ($point['width'] >= $width) {
                break;
            }
            $index++;
        }

        // no index, just return the base
        if (! $index) {
            return $data->get('base');
        }

        if ($index > count($this->breakpoints) - 1) {
            $index = count($this->breakpoints) - 1;
        }

        $output = [];
        for ($i = $index; $i >= 0; $i--) {
            if ($data->has($this->breakpoints[$i]['breakpoint'])) {
                return $data->get($this->breakpoints[$i]['breakpoint']);
            }
        }

        return $data->get('base');
    }
}
