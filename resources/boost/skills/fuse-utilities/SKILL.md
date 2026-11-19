---
name: fuse-utilities
description: "Use when building or changing front-end Blade templates, forms, scripts, SEO metadata or control panel blueprints in a Statamic site that uses the mitydigital/fuse-utilities package (Mity Digital's Fuse Starter Kit). Covers responsive images (the <x-fuse-utilities::image> component that project image components wrap, breakpoint widths and aspect ratios), private image URLs via ImageUrlGenerator, Bard and page builder rendering (Bard, PageBuilder, Html facades), search transformers, form helpers (Forms facade, captcha), script injection (<x-fuse-utilities::scripts>, Scripts facade), JSON-LD (StructuredMetadata facade), the @comment Blade directive, Dialog::registerId, the Theme base class, validation rules, custom control panel conditions and the fuse: artisan commands. Do not use for general Statamic or Laravel work that does not touch these features."
---

# Fuse Utilities

`mitydigital/fuse-utilities` is a Statamic 6 addon that provides helpers for sites built on Mity Digital's Fuse
Starter Kit. Many helpers assume the Starter Kit's content model: globals such as `site_defaults` and `forms`,
the package's fieldsets, and views such as `fuse.bard`. These are present in Starter Kit projects; do not assume
them in other projects.

## Before writing code

- Search the project for existing usage (for example `x-fuse-utilities::`, `Facades\Forms`, `StructuredMetadata::`)
  and follow the established pattern rather than inventing a new one.
- The package namespace is `MityDigital\FuseUtilities`. Views, components and fieldsets use the `fuse-utilities::`
  namespace.
- Configuration lives in `config/fuse-utilities.php` (publish with tag `fuse-utilities-config`).

## Pick the reference you need

| Task | Read |
|---|---|
| Render a responsive `<picture>`, set widths and aspect ratios per breakpoint, protect private images | [references/images.md](references/images.md) |
| Render Bard or page builder content to HTML or plain text, add search transformers | [references/content-and-search.md](references/content-and-search.md) |
| Build front-end forms: required fields, messages, submit labels, captcha | [references/forms.md](references/forms.md) |
| Inject global or per-page scripts, output JSON-LD and breadcrumbs, use the metadata fieldsets | [references/scripts-and-seo.md](references/scripts-and-seo.md) |
| `@comment`, `<x-fuse-utilities::tailwind>`, dialogs, themes, validation rules, control panel conditions | [references/blade-cp-and-validation.md](references/blade-cp-and-validation.md) |
| `fuse:path-references`, `fuse:generate-tailwind`, deletion guards, publishing | [references/commands-and-events.md](references/commands-and-events.md) |

## Rules

- Render content images through the project's image component when it has one that wraps
  `<x-fuse-utilities::image>` (the Fuse Starter Kit's is `<x-image>`); otherwise use `<x-fuse-utilities::image>`.
  Never hand-write `<picture>`, `srcset` or Glide tags. The package component's width prop is `width`, not `size`.
- Facades (`Bard`, `Forms`, `Html`, `PageBuilder`, `Scripts`, `StructuredMetadata`) resolve request-scoped services.
  State added during a request (for example `Scripts::add()`, `StructuredMetadata::add()`, `Forms::setForm()`) is
  only visible later in that same request, so add it before the layout reads it.
- Use the project's `App\Support\Theme` subclass, never `MityDigital\FuseUtilities\Support\Theme` directly.
- Only call public methods documented in these references. Do not call `protected` methods or rely on internal array
  shapes.
- To change a package view, publish it (`php artisan vendor:publish --tag=fuse-utilities-views`) and edit the
  published copy in `resources/views/vendor/fuse-utilities/`. Never edit files in `vendor/`.
- Do not use the `fuse-utilities::partials.*` views in new code.
