<?php

use Livewire\Livewire;
use MityDigital\FuseUtilities\Facades\Scripts as ScriptsFacade;
use MityDigital\FuseUtilities\Support\Scripts;
use MityDigital\FuseUtilities\Tests\Fixtures\ScriptsLivewireComponent;

/*
 * Livewire is optional for this package, so it is not a dependency. These tests run when it is installed, for example
 * with `composer require --dev livewire/livewire` (without committing the change), and skip otherwise.
 */
beforeEach(function () {
    if (! class_exists(Livewire::class)) {
        $this->markTestSkipped('Livewire is not installed.');
    }
});

it('loads code added while a Livewire component renders as a Livewire asset', function () {
    $component = Livewire::test(ScriptsLivewireComponent::class);

    expect($component->snapshot['memo']['assets'])->toBe([Scripts::LIVEWIRE_ASSET_KEY_PREFIX.'library']);
});

it('keeps the Livewire asset when the component updates', function () {
    $component = Livewire::test(ScriptsLivewireComponent::class)->call('$refresh');

    expect($component->snapshot['memo']['assets'])->toBe([Scripts::LIVEWIRE_ASSET_KEY_PREFIX.'library']);
});

it('does not output code in the layout once Livewire loads it', function () {
    ScriptsFacade::add('<script src="/layout-library.js"></script>', key: 'library');

    Livewire::test(ScriptsLivewireComponent::class);

    ScriptsFacade::add('<script src="/layout-library.js"></script>', key: 'library');

    expect(ScriptsFacade::get('body-end', scriptsContext()))->toBeNull();
});

it('outputs code in the layout when no Livewire component is rendering', function () {
    ScriptsFacade::add('<script src="/layout-library.js"></script>', key: 'library');

    expect(ScriptsFacade::get('body-end', scriptsContext()))->toBe('<script src="/layout-library.js"></script>');
});
