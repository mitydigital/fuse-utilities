<?php

namespace MityDigital\FuseUtilities\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MityDigital\FuseUtilities\Data\FormMessageData;
use MityDigital\FuseUtilities\Data\FormMessagesData;
use MityDigital\FuseUtilities\Exceptions\FormException;
use MityDigital\FuseUtilities\Facades\Bard;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Fields\Field;
use Statamic\Forms\Form;

class Forms
{
    protected $global;

    protected $form = null;

    public function setForm(Form $form)
    {
        $this->form = $form;
    }

    public function clearForm()
    {
        $this->form = null;
    }

    protected function global(): string
    {
        return 'forms';
    }

    public function __construct()
    {
        $this->loadGlobal();
    }

    public function isCaptchaEnabled(Form|string|null $form = null): bool
    {
        $forms = GlobalSet::findByHandle('forms')
            ->in(Site::current()->handle);

        $enabled = $forms->get('forms_'.(config('app.env')), false);

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

    protected function getField(array|string $handle): Field
    {
        if (! $this->form) {
            throw FormException::notSet();
        }

        if (is_array($handle)) {
            $handle = Arr::get($handle, 'handle');
        }

        if (! $handle) {
            throw FormException::missingHandle();
        }

        $field = $this->form->blueprint()->field($handle);

        if (! $field) {
            throw FormException::fieldNotFound($handle, $this->form->handle());
        }

        return $field;
    }

    public function isHoneypot(array|string $handle): bool
    {
        if (! $this->form) {
            throw FormException::notSet();
        }

        if (is_array($handle)) {
            $handle = Arr::get($handle, 'handle');
        }

        return $handle === $this->form->honeypot();
    }

    public function isFieldRequired(array|string $handle): bool
    {
        if ($this->isHoneypot($handle)) {
            return false;
        }

        $field = $this->getField($handle);

        return (bool) Arr::first($field->rules()[$handle] ?? [], fn (string $rule) => in_array(
            Str::before(strtolower($rule), ':'),
            ['required', 'required_if'],
            true,
        ));
    }

    public function getFieldShowRequiredConditions(array|string $handle): string
    {
        if ($this->isHoneypot($handle)) {
            return false;
        }

        $field = $this->getField($handle);

        foreach ($field->rules()[$handle] ?? [] as $rule) {
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

    public function isFieldConditional(array|string $handle): bool
    {
        if ($this->isHoneypot($handle)) {
            return false;
        }

        $field = $this->getField($handle);

        return Arr::hasAny($field->conditions(), ['if', 'unless']);
    }

    protected function loadGlobal(): void
    {
        $this->global = GlobalSet::findByHandle($this->global())
            ?->in(Site::current()->handle());
    }

    public function getMessages(): FormMessagesData
    {
        return new FormMessagesData(
            error: $this->getMessage('error'),
            success: $this->getMessage('success'),
            validation: $this->getMessage('validation'),
        );
    }

    public function getMessage(string $type): FormMessageData
    {
        if (! $this->form) {
            throw FormException::notSet();
        }

        $fieldHandle = 'default_'.$type;

        // get the default
        $message = $this->global->get($fieldHandle);

        // get the override
        foreach ($this->global->get('message_overrides', []) as $override) {
            if ($override['form'] === $this->form->handle() && $override['type'] === $type) {
                $message = $override['override'];
            }
        }

        return new FormMessageData(
            icon: Arr::get($message, 'icon'),
            heading: Arr::get($message, 'heading'),
            content: Bard::toHtml(Arr::get($message, 'content')),
        );
    }

    public function getButton(string $key = 'submit'): string
    {
        if (! $this->form) {
            throw FormException::notSet();
        }

        foreach ($this->global->get('submit_button_overrides', []) as $override) {
            if ($override['form'] === $this->form->handle()) {
                return $override['label'];
            }
        }

        return $this->global->get('submit_button');
    }
}
