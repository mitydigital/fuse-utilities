# Fuse Utilities for Statamic

<!-- statamic:hide -->

![Statamic 6.0+](https://img.shields.io/badge/Statamic-6.0+-FF269E?style=for-the-badge&link=https://statamic.com)
[![Fuse Utilities on Packagist](https://img.shields.io/packagist/v/mitydigital/fuse-utilities?style=for-the-badge)](https://packagist.org/packages/mitydigital/fuse-utilities/stats)

---
<!-- /statamic:hide -->

> Utilities for Mity Digital's Fuse Starter Kit.

These utilities are designed to work with Mity Digital's Fuse Starter Kit for Statamic.

## Commands

Symlink (for play site)

ln -s /Users/Marty/Code/starter-blade/vendor/fuse-utilities /Users/Marty/Code/statamic-addons/mitydigital/fuse-utilities/resources/dist

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

## Images

The Image helper has been created to make images easier to work with within the Tailwind landscape.

You can pass `aspect-ratio` and `size` attributes with Tailwind-like strings to help change image size and aspect
at different breakpoints. 

For example:
`<x-fuse-utilities::image ... aspect-ratio="3/2 md:1/1 lg:4/5" size="350 md:300 lg:900" />`

Here, the aspect will be 3:2 and 350px wide on the smallest devices, 1:1 and 300px wide next, and 4:5 and 900px wide over the `lg` breakpoint.

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


## Icons

The Starter Kit is configured for Heroicons. 

There is a command that helps set this up.

Basically this setup command helps hook up the Tailwind Icons with the site's Iconamic fieldtype.

On the frontend, we use Blade UI Icons, not Iconamic. But for the CP, we use Iconamic to get a preview of the icon. 

If Heroicons is not used, speak to Marty about setting up.

## Listeners

### Prevent Deleting Mounts

This runs before an Entry is deleted, and prevents a mount (such as "Blog") from being deleted.

This only runs when the app is in Production mode. In local dev mode, you can still delete mounts.

## Support and Facades

Supporting classes do the heavy lifting behind the scenes.

There are Facades available for `Bard`, `Forms`, `Scripts` and `StructuredMetadata`.

## Support

This is designed for use with Mity Digital's Starter Kit for Statamic, which is not a public Starter Kit. 

There is no support for provided for this Add-on.

## Credits

- [Marty Friedel](https://github.com/martyf)

## License

This addon is licensed under the MIT license.
