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

## Images

The Image helper has been created to make images easier to work with within the Tailwind landscape.

You can pass `aspect-ratio` and `size` attributes with Tailwind-like strings to help change image size and aspect
at different breakpoints. 

For example:
`<x-fuse-utilities::image ... aspect-ratio="3/2 md:1/1 lg:4/5" size="350 md:300 lg:900" />`

Here, the aspect will be 3:2 and 350px wide on the smallest devices, 1:1 and 300px wide next, and 4:5 and 900px wide over the `lg` breakpoint.

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

## Widgets


## Support

This is designed for use with Mity Digital's Starter Kit for Statamic, which is not a public Starter Kit. 

There is no support for provided for this Add-on.

## Credits

- [Marty Friedel](https://github.com/martyf)

## License

This addon is licensed under the MIT license.
