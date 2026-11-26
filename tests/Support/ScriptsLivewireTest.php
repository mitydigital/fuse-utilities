<?php

use Livewire\Component;
use Livewire\Livewire;
use MityDigital\FuseUtilities\Facades\Scripts as ScriptsFacade;
use MityDigital\FuseUtilities\Support\Scripts;

class ScriptsLivewireTestComponent extends Component
{
    public function render(): string
    {
        return <<<'BLADE'
            <div>
                @php(\MityDigital\FuseUtilities\Facades\Scripts::add('<script src="/livewire-library.js"></script>', key: 'library'))
            </div>
        BLADE;
    }
}

it('loads code added while a Livewire component renders as a Livewire asset', function () {
    $component = Livewire::test(ScriptsLivewireTestComponent::class);

    expect($component->snapshot['memo']['assets'])->toBe([Scripts::LIVEWIRE_ASSET_KEY_PREFIX.'library']);
});

it('keeps the Livewire asset when the component updates', function () {
    $component = Livewire::test(ScriptsLivewireTestComponent::class)->call('$refresh');

    expect($component->snapshot['memo']['assets'])->toBe([Scripts::LIVEWIRE_ASSET_KEY_PREFIX.'library']);
});

it('does not output code in the layout once Livewire loads it', function () {
    ScriptsFacade::add('<script src="/layout-library.js"></script>', key: 'library');

    Livewire::test(ScriptsLivewireTestComponent::class);

    ScriptsFacade::add('<script src="/layout-library.js"></script>', key: 'library');

    expect(ScriptsFacade::get('body-end', scriptsContext()))->toBeNull();
});

it('outputs code in the layout when no Livewire component is rendering', function () {
    ScriptsFacade::add('<script src="/layout-library.js"></script>', key: 'library');

    expect(ScriptsFacade::get('body-end', scriptsContext()))->toBe('<script src="/layout-library.js"></script>');
});
