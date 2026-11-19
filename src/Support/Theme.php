<?php

namespace MityDigital\FuseUtilities\Support;

use Illuminate\Support\Facades\View;
use Statamic\Fields\Values;

class Theme
{
    protected $lastTheme = null;

    protected $block = null;

    /**
     * @deprecated Only written by setTheme() and never read by this class. Kept so
     *             subclasses that reference it keep working.
     */
    protected $theme = null;

    public function hasBlock(): bool
    {
        return (bool) $this->block;
    }

    public function getLastTheme(): ?string
    {
        return $this->lastTheme;
    }

    public function setEntry($entry): void
    {
        View::share('entry', $entry);
    }

    public function setBlock($block): void
    {
        View::share('block', $block);

        $this->block = $block;
    }

    public function getBlock()
    {
        return $this->block;
    }

    public function clearBlock(): void
    {
        if ($this->block) {
            $this->lastTheme = $this->getTheme();
        }

        View::share('block', null);
        $this->block = null;
    }

    public function getTheme(): ?string
    {
        return $this->settings()->theme?->value();
    }

    /**
     * @deprecated This method has never had any effect: getTheme() always reads the current
     *             block's settings. Call clearBlock() after rendering a block instead, or pass
     *             a theme directly to the method that needs it. Will be removed in the next
     *             major version.
     */
    public function setTheme(?string $theme = null): void
    {
        $this->theme = $theme;
    }

    public function settings()
    {
        $settings = $this->block()->settings;

        if ($settings) {
            return $settings;
        }

        return new Values([]);
    }

    public function block(): Values
    {
        $block = View::shared('block');

        if ($block) {
            return $block;
        }

        return new Values([]);
    }
}
