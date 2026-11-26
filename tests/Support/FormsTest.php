<?php

use MityDigital\FuseUtilities\Exceptions\FormException;
use MityDigital\FuseUtilities\Support\Forms;
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
