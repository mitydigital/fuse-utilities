<?php

namespace MityDigital\FuseUtilities\Tags\Concerns;

use Illuminate\Support\Str;
use MityDigital\FuseUtilities\Facades\FuseUtilities;
use Statamic\Facades\Antlers;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Fieldtypes\Bard;
use Statamic\Fieldtypes\Bard\Augmentor;

trait Form
{
    public function isCaptchaEnabled(): bool
    {
        return FuseUtilities::isCaptchaEnabled(
            environment: $this->context->get('environment'),
            form: $this->params->get('form', null),
            site: $this->context->get('site')->handle
        );
    }

    public function siteName(): string
    {
        $handle = $this->context->get('site', '/');
        $site = Site::get($handle);

        if ($site) {
            return $site->name();
        }

        return $handle;
    }

    public function isFormFieldConditional(): bool
    {
        foreach ($this->context->get('fields', []) as $field) {
            if (array_key_exists('if', $field)) {
                return true;
            }
        }

        return false;
    }

    public function isFormFieldRequired(): bool|string
    {
        foreach ($this->getFormFieldValidation() as $rule) {
            // required - easy
            if ($rule === 'required') {
                return true;
            }

            // required_if - check further
            if (Str::startsWith($rule, 'required_if:')) {
                // get the field and value
                [$field, $value] = explode(',', substr($rule, 12));

                return 'form.'.$field." == '".$value."'";
            }
        }

        return false;
    }

    protected function getFormFieldValidation(): array
    {
        if ($this->context->get('field', null)) {
            return $this->context->get('validate', []);
        } else {
            $handle = $this->params->get('field', null);
            if (!$handle) {
                throw new Exception('Missing "field" parameter in fuse:is_form_field_required.');
            }

            foreach ($this->context->get('fields', []) as $field) {
                if (is_array($field) && array_key_exists('handle', $field) && $field['handle'] === $handle) {
                    if (array_key_exists('validate', $field)) {
                        return $field['validate'];
                    }
                }
            }
        }

        return [];
    }

    public function getFormLang()
    {
        $form = $this->params->get('form', null);

        // wig out if there is no "form" parameter
        if (!$form) {
            throw new Exception('Missing "form" parameter in fuse:get_form_lang.');
        }

        $type = $this->params->get('type', null);

        // wig out if there is no "type" parameter
        if (!$type) {
            throw new Exception('Missing "type" parameter in fuse:get_form_lang.');
        }

        // get the defaults
        $global = GlobalSet::findByHandle('forms')
            ->in($this->context->get('site')->handle);

        //
        // submit button
        //
        if ($type === 'submit') {
            foreach ($global->get('submit_button_overrides', []) as $override) {
                if ($override['form'] === $form) {
                    return $override['label'];
                }
            }

            return $global->get('submit_button');
        }

        //
        // labels (success, error, validation)
        //
        $fieldHandle = 'default_message_'.$type;

        // get the default
        $message = $global->get($fieldHandle);

        $handle = $form;
        if (!is_string($handle) && method_exists($handle, 'handle')) {
            $handle = $form->handle();
        }

        // get the override
        foreach ($global->get('message_overrides', []) as $override) {
            if ($override['form'] === $handle && $override['type'] === $type) {
                $message = $override['message'];
            }
        }

        if (is_array($message)) {
            $bard = (new Bard())->setField($global->blueprint()->field($fieldHandle));

            $content = (new Augmentor($bard))
                ->augment($message);

            return $content;
        } else {
            return Antlers::parse($message)->__toString();
        }
    }
}
