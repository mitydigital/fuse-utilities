# Bard, page builder and search

All facades live in `MityDigital\FuseUtilities\Facades`.

## Bard

```php
use MityDigital\FuseUtilities\Facades\Bard;

Bard::toHtml($entry->content);                       // Bard value to HTML, Antlers parsed
Bard::toHtml($value, antlers: false);                 // skip Antlers parsing
Bard::toHtml($value, context: ['name' => 'Jane']);    // extra variables for Antlers

Bard::render($value);                    // renders the project view `fuse.bard` with $content
Bard::render($value, view: 'fuse.bard_simple');
Bard::render($value, text: true);        // rendered view converted to plain text
```

- `toHtml()` returns `''` for empty content. When `antlers` is true it parses with the current Cascade and site, so
  Antlers in Bard text (e.g. `{{ site:name }}`) works. Use `antlers: false` for untrusted content.
- `render()` uses a Blade view that the **project** provides (`resources/views/fuse/bard.blade.php` in the Starter
  Kit). It receives the value as `$content`. Use it when sets inside Bard need the project's own partials.

## Page builder

```php
use MityDigital\FuseUtilities\Facades\PageBuilder;

PageBuilder::toHtml($entry->page_builder);   // renders project view `fuse.page_builder` with $page_builder
PageBuilder::toText($entry->page_builder);   // same, as plain text
PageBuilder::toHtml($blocks, 'fuse.page_builder_email');
```

Both expect an array (a replicator value). The view is the project's, not the package's.

## HTML to text

```php
use MityDigital\FuseUtilities\Facades\Html;

Html::toText($html); // strips tags, decodes entities, collapses whitespace
```

Use for meta descriptions, search content and previews.

## Search transformers

Index rich fields as plain text by adding transformers to an index in `config/statamic/search.php`:

```php
'default' => [
    'driver' => 'local',
    'searchables' => ['collection:pages'],
    'fields' => ['title', 'content', 'page_builder'],
    'transformers' => [
        'content' => \MityDigital\FuseUtilities\SearchTransformers\BardTransformer::class,
        'page_builder' => \MityDigital\FuseUtilities\SearchTransformers\PageBuilderTransformer::class,
    ],
],
```

- `BardTransformer` returns `Bard::render(..., text: true)`, or `null` when the field is empty.
- `PageBuilderTransformer` returns `PageBuilder::toText(...)`.
- Both render the project views above, so those views must work outside a page request (no reliance on
  request-only variables). Re-index after changing them: `php please search:update --all`.
