# Scripts, structured data and metadata fieldsets

## Script injection

The layout outputs scripts at three locations. Each call passes the view's variables as context:

```blade
<head>
    ...
    <x-fuse-utilities::scripts location="head" :context="get_defined_vars()" />
</head>
<body>
    <x-fuse-utilities::scripts location="body-start" :context="get_defined_vars()" />
    ...
    <x-fuse-utilities::scripts location="body-end" :context="get_defined_vars()" />
</body>
```

`location` must be `head`, `body-start` or `body-end`; anything else throws. Output is raw HTML, or nothing.

Sources, in output order:

1. **Site-wide**: the `javascripts` replicator on the `site_defaults` global.
2. **Per page**: the entry's `javascripts` field.
3. **Added in code** during the request, with `Scripts::add()`. It takes a "Script" entry or a string of HTML:

   ```php
   use MityDigital\FuseUtilities\Facades\Scripts;

   Scripts::add($scriptEntry); // a Statamic Entry with `location` and `code` fields

   Scripts::add('<script src="/vendor/library.js" data-navigate-once></script>'); // body-end
   Scripts::add('<script src="/vendor/library.js"></script>', 'head', key: 'library');
   ```

   - An entry uses its own `location`, and its id as the key. An entry without a valid location is ignored.
   - A string goes to `$location` (default `body-end`; anything other than `head`, `body-start` or `body-end` throws).
     Its key defaults to a hash of the code, so the same code is only output once. Pass `key:` when different code
     should replace it, or to make the intent clear.
   - Each key is output once per request, however many times it is added (no `@once` needed).
   - Added scripts are output after sources 1 and 2, and ignore the environment rules below.
   - Call `add()` before the layout renders that location. A component or block in the page body is fine: the body
     renders before the layout.
   - Use it instead of outputting a `<script>` from a component or block.

   **Livewire.** When Livewire is installed and a Livewire component is rendering, `add()` loads the script as a
   Livewire asset (as `@assets` does) instead of through the layout. Livewire then loads it once per page, in the
   `<head>` (so `$location` is ignored), including when the component renders in a Livewire update (lazy and deferred
   components, islands, components a parent adds). Sites without Livewire, and code outside Livewire components, use
   the layout as above. With `wire:navigate`, Livewire runs `<body>` scripts again on every page; add
   `data-navigate-once` to a library's `<script>` tag so it only runs once.

Environment rules for sources 1 and 2:

- A script must have `location` matching and be enabled (replicator set toggle).
- With `environment.follow: default`, it outputs when the `site_defaults` toggle for the current `APP_ENV` is on:
  `javascript_local`, `javascript_staging` (both default off) or `javascript_production` (default on).
- With `environment.follow: custom`, the script's own `enabled_on_local` / `enabled_on_staging` /
  `enabled_on_production` toggle decides.
- Other environment names (e.g. `testing`) only output scripts with a matching custom toggle.

Add the `javascripts` replicator to blueprints by importing the package fieldset rather than recreating it:

```yaml
- import: fuse-utilities::shared_scripts
```

Script code is validated with `IsCodeFieldtypeHtmlRule`, so it must be HTML (include the `<script>` tags).

## JSON-LD structured data

`MityDigital\FuseUtilities\Facades\StructuredMetadata` collects JSON-LD for the page. Check the project's layout for
the existing output before adding another one; there should be a single place that outputs it.

```php
use MityDigital\FuseUtilities\Facades\StructuredMetadata;

// From a block or view, before the layout outputs metadata:
StructuredMetadata::add([
    '@context' => 'https://schema.org',
    '@type' => 'Event',
    'name' => $title,
    'image' => '{{ $image }}',
], image: $heroAsset);

// In the layout: returns a list of pretty-printed JSON strings (no <script> tags)
foreach (StructuredMetadata::get($siteSources, $pageSources) as $json) { ... }

// Breadcrumbs: JSON string, or false when disabled
StructuredMetadata::breadcrumbs(get_defined_vars());
```

- `add(array $data, $image = null)`: `$data` is the JSON-LD as an array.
- `get(array ...$sources)` returns items added with `add()` first, then each source array. Each source item has
  `code` (array or JSON string) and optional `image`; this is the shape stored by the
  `fuse-utilities::shared_metadata_structured` fieldset's `structured_data` replicator.
- When an image is given, `{{ $image }}` or `{{ image }}` in the JSON is replaced with an absolute Glide URL
  (1080×1080 for `Organization`, 1920×1080 otherwise).
- `breadcrumbs($context)` needs `site_defaults->breadcrumbs` on, the page's `breadcrumbs` toggle not turned off, and
  a `segment_1` (it is skipped on the home page). Output it inside `<script type="application/ld+json">`.

## Metadata fieldsets

Import these in collection blueprints instead of defining SEO fields by hand:

| Fieldset | Provides |
|---|---|
| `fuse-utilities::shared_metadata` | `meta_title`, `meta_description`, `meta_include_title_suffix`, social title/description/`og_image`, structured data, `noindex`, `nofollow`, canonical (`canonical_type`, `canonical_entry`, `canonical_url`), sitemap fields |
| `fuse-utilities::shared_metadata_structured` | `structured_data` replicator (JSON-LD `code` plus `image`) and `breadcrumbs` toggle |
| `fuse-utilities::shared_scripts` | `javascripts` replicator |
| `fuse-utilities::shared_script` | One script's `location`, `environment` and `code` fields |

To customise one, publish with `php artisan vendor:publish --tag=fuse-utilities-fieldsets` and edit the copy in
`resources/fieldsets/vendor/fuse-utilities/`.
