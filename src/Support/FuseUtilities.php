<?php

namespace MityDigital\FuseUtilities\Support;

use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Forms\Form;

class FuseUtilities
{
    public function getImagesMissingAltCache(): string
    {
        return 'fuse-utilities::widgets.images-without-alt';
    }

    public function isCaptchaEnabled(?string $site = null, ?string $environment = null, Form|string|null $form = null): bool
    {
        $forms = GlobalSet::findByHandle('forms')
            ->in($site ?? Site::current()->handle);

        $enabled = $forms->get('forms_'.($environment ?? config('app.env')), false);

        // if enabled, check that the form isn't excluded
        if ($enabled && $form) {
            $excludedForms = $forms->get('excluded_forms', []);

            $handle = $form;
            if (is_object($form) && get_class($form) === Form::class) {
                $handle = $form->handle();
            }

            if (in_array($handle, $excludedForms)) {
                return false;
            }
        }

        return $enabled;
    }
}
