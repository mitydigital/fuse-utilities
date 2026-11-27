<?php

use MityDigital\FuseUtilities\Exceptions\FormException;
use MityDigital\FuseUtilities\Support\Forms;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;

it('prefixes field ids with the form handle and instance', function () {
    $forms = new Forms;

    $forms->setForm(Form::make('contact'));

    expect($forms->getIdPrefix())->toBe('contact_1_')
        ->and($forms->getFieldId('email'))->toBe('contact_1_email')
        ->and($forms->getFieldId(['handle' => 'email']))->toBe('contact_1_email');
});

it('gives each instance of the same form its own prefix', function () {
    $forms = new Forms;

    $forms->setForm(Form::make('contact'));
    $first = $forms->getFieldId('email');
    $forms->clearForm();

    $forms->setForm(Form::make('contact'));
    $second = $forms->getFieldId('email');
    $forms->clearForm();

    expect($first)->toBe('contact_1_email')
        ->and($second)->toBe('contact_2_email');
});

it('counts instances separately for each form', function () {
    $forms = new Forms;

    $forms->setForm(Form::make('contact'));
    $forms->clearForm();

    $forms->setForm(Form::make('newsletter'));

    expect($forms->getIdPrefix())->toBe('newsletter_1_');
});

it('throws for an id prefix when no form is set', function () {
    $forms = new Forms;

    $forms->setForm(Form::make('contact'));
    $forms->clearForm();

    $forms->getIdPrefix();
})->throws(FormException::class);

it('throws for a field id without a handle', function () {
    $forms = new Forms;

    $forms->setForm(Form::make('contact'));

    $forms->getFieldId(['display' => 'Email']);
})->throws(FormException::class);

/**
 * Makes a "contact" form whose blueprint has the given fields.
 *
 * @param  array<string, array<string, mixed>>  $fields
 */
function makeFormWithFields(array $fields): Statamic\Contracts\Forms\Form
{
    Blueprint::make('contact')
        ->setNamespace('forms')
        ->setContents(['fields' => collect($fields)->map(fn (array $field, string $handle) => [
            'handle' => $handle,
            'field' => $field,
        ])->values()->all()])
        ->save();

    return Form::make('contact');
}

it('reads required from string rules', function () {
    $forms = new Forms;

    $forms->setForm(makeFormWithFields([
        'name' => ['type' => 'text', 'validate' => ['required']],
        'company' => ['type' => 'text', 'validate' => ['required_if:type,business']],
        'notes' => ['type' => 'textarea'],
    ]));

    expect($forms->isFieldRequired('name'))->toBeTrue()
        ->and($forms->isFieldRequired('company'))->toBeTrue()
        ->and($forms->isFieldRequired('notes'))->toBeFalse()
        ->and($forms->getFieldShowRequiredConditions('name'))->toBeTrue()
        ->and($forms->getFieldShowRequiredConditions('company'))->toBe("form.type == 'business'")
        ->and($forms->getFieldShowRequiredConditions('notes'))->toBeFalse();
});

it('ignores rule objects a fieldtype adds when reading required', function () {
    $forms = new Forms;

    $forms->setForm(makeFormWithFields([
        'start' => ['type' => 'time', 'validate' => ['required']],
        'finish' => ['type' => 'time'],
    ]));

    expect($forms->isFieldRequired('start'))->toBeTrue()
        ->and($forms->isFieldRequired('finish'))->toBeFalse()
        ->and($forms->getFieldShowRequiredConditions('start'))->toBeTrue()
        ->and($forms->getFieldShowRequiredConditions('finish'))->toBeFalse();
});
