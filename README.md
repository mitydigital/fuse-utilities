# Fuse Utilities for Statamic

<!-- statamic:hide -->

![Statamic 5.0+](https://img.shields.io/badge/Statamic-5.0+-FF269E?style=for-the-badge&link=https://statamic.com)
[![Supportamic on Packagist](https://img.shields.io/packagist/v/mitydigital/fuse-utilities?style=for-the-badge)](https://packagist.org/packages/mitydigital/fuse-utilities/stats)

---
<!-- /statamic:hide -->

> Utilities for Mity Digital's Fuse Starter Kit.

These utilities are designed to work with Mity Digital's Fuse Starter Kit for Statamic.

## Commands

### Generate Tailwind

The Generate Tailwind command can be run by running:
```shell
php please fuse:generate-tailwind
```

This will create the `resources/tailwind/generated.html` file based on the `resources/tailwind/config.yaml` file.

In the config file, there are two sections - `colours` and `classes`.

For `colours`, this is a list of the different colour handles you want to apply to the Tailwind classes. This is as 
simple as colours like `red` or `amber` or `sky` - whatever they are called in the `tailwind.config.js` file.

For `classes`, this will list the Tailwind classes that you want to have generated for the range of colours provided. 
With each class, replace the colour with the string `[colour]` to inject the different colour classes. This means that
`text-[colour]-500` will output:
- `text-red-500`
- `text-amber-500`
- `text-sky-500`

This is intended to make working with colours easier by generating the different classes to a HTML file for the 
Tailwind process to pick up and include in the site's CSS.

## Listeners

### Prevent Deleting Mounts

This runs before an Entry is deleted, and prevents a mount (such as "Blog") from being deleted.

This only runs when the app is in Production mode. In local dev mode, you can still delete mounts.

## Tags

There is a `fuse` tag that includes functionality for the utilities.

### Forms

#### isCaptchaEnabled

Returns a boolean as to whether Captcha is enabled for the current configuration.

```antlers
{{ fuse:is_captcha_enabled }}
```

You can also pass a `form` parameter to check if Captcha is enabled for the specified form.

```antlers
{{ fuse:is_captcha_enabled form="handle" }}
```

#### getFormLang

Returns a language string or override for a form and type.

```antlers
{{ fuse:get_form_lang type="submit" :form="form"
```

The `form` parameter is the form's handle. `type` is one of:
- `success`, the success message
- `validation`, the validation issue message
- `error`, the error when submission fails
- `submit`, the submit button label

#### isFormFieldConditional

Returns whether a form field is conditional:

```antlers
{{ fuse:is_form_field_required }}
```

By default will use the current context, but can also pass a field handle:

```antlers
{{ fuse:is_form_field_required :field="handle" }}
```

#### isFormFieldRequired

Returns whether a form field is required.

Returns true when `required`, a string of required logic when `required_if` or false if not required at all.

```antlers
{{ fuse:is_form_field_required :field="handle" }}
```

#### siteName

Returns the site's name in the current context.
```antlers
{{ fuse:site_name }}
```

### Image

The `image` tag returns dynamic configuration options for use with the image component.

```antlers
{{ fuse:image :url="image" }}
```

It will return all of the Asset's properties (just like the existing `asset` tag), plus additional properties
to help with the `<picture>` element.

It will take advantage of these params or context. If a param is not found, the context will be used:
- `aspect_ratio`
- `min`
- `max`
- `quality`
- `sizes`

The image component accepts these parameters:
- `aspect_ratio`, a string of responsive aspect ratios
- `cover`, default `false`, or set to `true` to fill the parent container
- `lazy`, default `false`, or set to `true` to lazy load image
- `min`, the minimum size to render, default to 320
- `max`, the maximum size to render, default to 1440
- `quality`, a string of responsive qualities
- `sizes`, override the sizes property

The `aspect_ratio` and `quality` properties accept the standard Tailwind prefixes for responsive behaviour, such as:
```antlers
{{ partial:components/image quality="80 sm:75 lg:70 xl:60" }}
```

Note that behaviour will differ based on screen pixel density too.

You can use `sm`, `md`, `lg`, `xl`, `2xl`, `3xl`, `4xl` and `5xl`.

The `sizes` property defaults to `(min-width: 768px) 50vw, 100vw` but can be overridden per use case.

### JSON Schema

Site metadata uses the JSON Schema function to output any schema details for the site. This is done by calling:
```antlers
{{ fuse:json_schema }}
```

Currently this is only used in `resources/views/partials/metadata/_json-ld.antlers.html`.

### Scripts

The Scripts method allows you to include the `head` or `body` scripts. This can be used by passing one of these as
the location parameter:
```antlers
{{ fuse:scripts location="head" }}
```

## Widgets

### Images without Alt

The Images without Alt widget can be added to the Dashboard using the `images_without_alt` key in your Widgets 
configuration:
```php
[
    'type' => 'images_without_alt',
    'containers' => ['assets', 'site']
    'limit' => 5,
    'expiry' => 60,
    'width' => 50,
],
```

You can adjust the configuration of the widget using these options:
- `containers`: optional, will use all Containers when omitted. Or an array of container handles to include
- `limit`: the number of assets to show on the widget
- `expiry`: the duration of the cache for the widget. Will get automatically refreshed as assets change
- `width`: the width of the widget on your Dashboard

## Add-on Support

This is designed for use with Mity Digital's Starter Kit for Statamic, which is not a public Starter Kit. 

There is no support for provided for this Add-on.

## Credits

- [Marty Friedel](https://github.com/martyf)

## License

This addon is licensed under the MIT license.
