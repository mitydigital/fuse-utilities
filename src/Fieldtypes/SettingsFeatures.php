<?php

namespace MityDigital\FuseUtilities\Fieldtypes;

use Statamic\Fieldtypes\Hidden;

class SettingsFeatures extends Hidden
{
    protected $icon = 'star';

    public function process($data)
    {
        return null;
    }

    public function indexComponent(): string
    {
        return $this->component();
    }

    public function component(): string
    {
        return 'hidden';
    }
}
