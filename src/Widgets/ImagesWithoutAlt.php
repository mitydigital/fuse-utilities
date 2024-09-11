<?php

namespace MityDigital\FuseUtilities\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use MityDigital\FuseUtilities\Facades\FuseUtilities;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Widgets\Widget;

class ImagesWithoutAlt extends Widget
{
    /**
     * The HTML that should be shown in the widget.
     *
     * This has been inspired from Studio 1902's Peak starter kit
     *
     * @return string|View
     */
    public function html()
    {
        $expiration = Carbon::now()->addMinutes($this->config('expiry', 0));

        $data = Cache::remember(FuseUtilities::getImagesMissingAltCache(), $expiration, function () {

            $containers = $this->config('container', null);
            if (! $containers) {
                // get all containers
                $containers = AssetContainer::all()->pluck('handle')->toArray();
            } else {
                // ensure it is an array
                $containers = Arr::wrap($containers);
            }

            $assets = collect($containers)
                ->map(fn (string $container) => Asset::query()
                    ->where('container', $container)
                    ->whereNull('alt')
                    ->where('is_image', true)
                    ->orderBy('last_modified', 'desc')
                    ->limit(100)
                    ->get()
                    ->toAugmentedArray()
                )
                ->flatten(1)
                ->sortByDesc('last_modified_timestamp')
                ->values();

            return collect([
                'assets' => collect($assets),
                'containers' => collect($containers),
            ]);
        });

        return view('fuse-utilities::widgets.images-without-alt', [
            'assets' => $data->get('assets')->slice(0, $this->config('limit', 5)),
            'count' => $data->get('assets')->count(),
            'container' => $data->get('containers')->count() === 1 ? AssetContainer::findByHandle($data->get('containers')->first()) : null,
        ]);
    }
}
