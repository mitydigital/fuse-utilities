<div class="card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-4">
        <h2 class="flex items-center">
            <div class="h-6 w-6 rtl:ml-2 ltr:mr-2 text-gray-800">
                @cp_svg('icons/light/assets')
            </div>
            <span>{{ __('fuse-utilities::widgets.images_without_alt.title') }}</span>
        </h2>
        <a aria-label="{{ __('fuse-utilities::widgets.images_without_alt.browse') }}"
           class="btn-primary"
           href="{{ $container ? $container->showUrl() : cp_route('assets.index') }}">
            {{ __('fuse-utilities::widgets.images_without_alt.browse') }}
        </a>
    </div>
    @if ($count > 0)
        <div class="content px-4 pb-2">
            <p class="text-xs">{{ __('fuse-utilities::widgets.images_without_alt.explanation') }}</p>
            <p>{{ trans_choice('fuse-utilities::widgets.images_without_alt.count', $count, ['count' => $count]) }}</p>
        </div>
    @else
        <div class="content flex px-4 pb-2">
            <div class="size-4 mr-2 text-green-600" style="flex-shrink: 0">
                @cp_svg('icons/light/check')
            </div>
            <p>{{ __('fuse-utilities::widgets.images_without_alt.done') }}</p>
        </div>
    @endif

    @if ($assets)
        <table tabindex="0" class="data-table">
            <tbody tabindex="0">

            @foreach ($assets as $asset)
                <tr class="sortable-row outline-none" tabindex="0">
                    <td>
                        <div class="flex items-center">
                            <div class="little-dot mr-2 bg-red-500"></div>
                            <a href="{{ $asset['edit_url'] }}"
                               class="flex w-full group"
                               aria-label="{{ __('fuse-utilities::widgets.images_without_alt.assets_edit') }}">
                                <span class="flex-grow">{{ $asset['basename'] }}</span>
                            </a>
                        </div>
                    </td>
                    <td class="actions-column w-0"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if ($count > $assets->count())
            <div class=" px-3 pt-1 pb-2">
                <a aria-label="{{ __('fuse-utilities::widgets.images_without_alt.browse_label') }}"
                      href="{{ $container ? $container->showUrl() : cp_route('assets.index') }}"
                      class="btn btn-xs">
                    {{ __('fuse-utilities::widgets.images_without_alt.browse_long') }}
                </a>
            </div>
        @endif
    @endif
</div>
