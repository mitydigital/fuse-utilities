<?php

namespace MityDigital\FuseUtilities\Tests\Fixtures;

use Livewire\Component;

/**
 * Only loaded by the Livewire tests, which skip when Livewire is not installed.
 */
class ScriptsLivewireComponent extends Component
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
