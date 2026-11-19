# Fuse Utilities for Statamic

<!-- statamic:hide -->

![Statamic 6.0+](https://img.shields.io/badge/Statamic-6.0+-FF269E?style=for-the-badge&link=https://statamic.com)
[![Fuse Utilities on Packagist](https://img.shields.io/packagist/v/mitydigital/fuse-utilities?style=for-the-badge)](https://packagist.org/packages/mitydigital/fuse-utilities/stats)

---
<!-- /statamic:hide -->

> Utilities for Mity Digital's Fuse Starter Kit.

These utilities are designed to work with Mity Digital's Fuse Starter Kit for Statamic. Many helpers expect the
globals, fieldsets and views that the Starter Kit installs.

## AI agents (Laravel Boost)

This package ships a [Laravel Boost](https://github.com/laravel/boost) guideline and a `fuse-utilities` skill in
`resources/boost/`. Select `mitydigital/fuse-utilities` when running `php artisan boost:install` (or
`boost:update`) and Boost will install them for your agents, such as Claude Code and Codex.

## Commands

### Find assets (Path References)

Use this command to create a list of files that contain a reference to the path. The default command looks for `temp/` (i.e. temp assets)

```shell
php artisan fuse:path-references
```

You can pass your own path if you need too:
```shell
php artisan fuse:path-references a/different/path 
```

This command will look at the site's source, and follow the `.gitignore` rules.

### Generate Tailwind safelist

Reads `resources/tailwind/config.yaml` and writes `resources/tailwind/generated.html`, a file of complete class names
for a set of colours and classes.

```shell
php artisan fuse:generate-tailwind
```

The YAML must set `version: 4`, and include `colours`, `classes` (using `[colour]` as the placeholder) and,
optionally, `standard`.

## Images

The Image helper has been created to make images easier to work with within the Tailwind landscape.

You can pass `width` and `aspect-ratio` attributes with Tailwind-like strings to change the image width and aspect
at different breakpoints.

For example:

```blade
<x-fuse-utilities::image :asset="$image" width="350 md:300 lg:900" aspect-ratio="3/2 md:1/1 lg:4/5" />
```

Here, the aspect will be 3:2 and 350px wide on the smallest devices, 1:1 and 300px wide from `md`, and 4:5 and 900px
wide from `lg`.

| Prop            | Default      | Notes                                                                                                                         |
|-----------------|--------------|-------------------------------------------------------------------------------------------------------------------------------|
| `asset`         | required     | A Statamic `Asset`.                                                                                                           |
| `width`         | `null`       | Tailwind-like widths in pixels, e.g. `350 md:600`. When omitted, widths come from `default_width` and the configured breakpoints. |
| `aspect-ratio`  | `null`       | Tailwind-like `width/height` ratios, e.g. `16/9 lg:3/1`. When omitted, the asset's own ratio is used.                         |
| `quality`       | `null`       | Tailwind-like Glide quality, e.g. `80 lg:70`.                                                                                 |
| `fit`           | `crop_focal` | Glide fit. `crop_focal` uses the asset's focal point.                                                                         |
| `sizes`         | `null`       | Overrides the generated `sizes` attribute, e.g. `100vw` for fluid layouts.                                                    |
| `alt`           | asset `alt`  | Overrides the asset's alt text.                                                                                               |
| `class`         | `null`       | Classes for the `<img>`.                                                                                                      |
| `picture-class` | `null`       | Classes for the `<picture>`.                                                                                                  |
| `style`         | `null`       | Inline style for the `<img>`.                                                                                                 |
| `loading`       | `lazy`       |                                                                                                                               |
| `decoding`      | `async`      |                                                                                                                               |

Breakpoints, the default width, the default fit and pixel densities are set in `config/fuse-utilities.php`.

### Private images

You can adjust the way that private assets get routed so that they can use a custom route, then get passed back to
Statamic. This allows things like access control to be performed in the controller, which could even be signed and with
an expiry date, and then Statamic can still internally handle its rendering.

To use a protected route for a private container:

1. Create an implementation of `MityDigital\FuseUtilities\Contracts\ImageUrlGenerator`.
2. Return the package’s default URL generator for public assets, and your protected route URL for the private container.
3. Bind your implementation to `ImageUrlGenerator::class` in your application's service provider.
4. In the protected route controller, check access to the asset before serving the original image or passing validated image parameters to Statamic's `ImageGenerator`.

The generator receives the asset and the requested Glide parameters, so responsive sizes, crops, quality settings, and image formats continue to work as normal.

For example
```php
<?php

namespace App\Images;

use App\Support\Product;
use MityDigital\FuseUtilities\Contracts\ImageUrlGenerator;
use MityDigital\FuseUtilities\Support\StatamicImageUrlGenerator;
use Statamic\Contracts\Assets\Asset;
use Statamic\Support\Str;

class ProjectImageUrlGenerator implements ImageUrlGenerator
{
    public function __construct(
        private StatamicImageUrlGenerator $defaultGenerator,
    ) {}

    public function generate(Asset $asset, array $params = []): string
    {
        if ($asset->container()->handle() !== 'private') {
            return $this->defaultGenerator->generate($asset, $params);
        }

        return url()->temporarySignedRoute('account.child.asset.image',
            now()->addHours(2),
            [
                'asset' => Str::toBase64Url($asset->id()),
                /* any other params that need to be added */
                ...$params,
            ]);
    }
}
```

Then in the `AppServiceProvider.php`:
```php
use App\Images\ProjectImageUrlGenerator;
use MityDigital\FuseUtilities\Contracts\ImageUrlGenerator;

$this->app->bind(ImageUrlGenerator::class, ProjectImageUrlGenerator::class);
```

## Listeners

### Prevent Deleting Mounts

This runs before an Entry is deleted, and prevents a mount (such as "Blog") from being deleted.

This only runs when the app is in Production mode. In local dev mode, you can still delete mounts.

## Support and Facades

Supporting classes do the heavy lifting behind the scenes.

There are Facades available for `Bard`, `Forms`, `Html`, `PageBuilder`, `Scripts` and `StructuredMetadata`.

`Support\Theme` is a base class: the Starter Kit extends it as `App\Support\Theme`. `Support\Dialog::registerId()`
ensures dialog ids are unique within a request.

## Support

This is designed for use with Mity Digital's Starter Kit for Statamic, which is not a public Starter Kit. 

There is no support for provided for this Add-on.

## Credits

- [Marty Friedel](https://github.com/martyf)

## License

This addon is licensed under the GPL-3.0 license.
