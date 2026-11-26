# Forms

`MityDigital\FuseUtilities\Facades\Forms` helps render Statamic forms on the front end. It reads the Starter Kit's
`forms` global set for the current site.

## Lifecycle

Most methods need a form to be set first, otherwise they throw `MityDigital\FuseUtilities\Exceptions\FormException`
("Form not set."). The service is request-scoped, so set the form before rendering its fields and clear it after:

```blade
@php(\MityDigital\FuseUtilities\Facades\Forms::setForm($form))

{{-- render fields, messages and the submit button --}}

@php(\MityDigital\FuseUtilities\Facades\Forms::clearForm())
```

`$form` is a `Statamic\Forms\Form` (e.g. `Statamic\Facades\Form::find('contact')`).

## Element ids

Field ids come from field handles, so rendering the same form twice on one page would repeat every id, and the second
form's labels would point at (and toggle) the first form's controls. Each `setForm()` call starts a new instance of
the form with its own id prefix: `{form_handle}_{n}_`, counted per form handle for the request.

| Method | Returns |
|---|---|
| `getIdPrefix()` | The current form's prefix, such as `contact_1_` (then `contact_2_` for the next `contact` form). |
| `getFieldId($field)` | The prefixed id for a field handle (or a field array, or any other string), such as `contact_1_email`. |

Use `getFieldId()` for every element `id` and label `for` in the form, and keep the input `name` as the bare handle.
Both throw `FormException` when no form is set. Script that finds a field by id (such as an error summary link)
should read the prefix from the form element, for example from a `data-id-prefix` attribute.

## Field helpers

Each accepts a field handle, or a field array containing a `handle` key (as you get when looping over form fields).

| Method | Returns |
|---|---|
| `isHoneypot($field)` | `true` when the field is the form's honeypot. |
| `isFieldRequired($field)` | `true` when the field has `required` or `required_if` rules. Always `false` for the honeypot. |
| `getFieldShowRequiredConditions($field)` | `true` (always required), `false` (not required), or for `required_if:other,value` an Alpine expression string `form.other == 'value'`. |
| `isFieldConditional($field)` | `true` when the field has `if` or `unless` conditions. |

`FormException` is also thrown when the handle is missing or the field is not on the form's blueprint.

## Messages and buttons

```php
$messages = Forms::getMessages();      // FormMessagesData with ->error, ->success, ->validation
$success = Forms::getMessage('success'); // FormMessageData with ->icon, ->heading, ->content (HTML)
$label = Forms::getButton();           // submit button label
```

Values come from the `forms` global: `default_error`, `default_success` and `default_validation` (each with
`icon`, `heading` and Bard `content`), replaced per form by matching rows in `message_overrides` (`form`, `type`,
`override`). The button label is `submit_button`, replaced per form by `submit_button_overrides` (`form`, `label`).
Edit those globals in the control panel rather than hard-coding text in templates.

## Captcha

`Forms::isCaptchaEnabled($form = null)` returns the `forms` global's `forms_{APP_ENV}` toggle (e.g.
`forms_production`), and `false` when the form's handle is listed in `excluded_forms`. It accepts a `Form` or a
handle and does not need `setForm()`.

To make `aryehraber/statamic-captcha` follow the same rules on submission, set in `config/captcha.php`:

```php
'custom_should_verify' => \MityDigital\FuseUtilities\Contracts\CaptchaShouldVerify::class,
```

Despite its namespace, `CaptchaShouldVerify` is a ready-made class, not an interface to implement.
