# Blade helpers, dialogs, themes, validation and control panel

## `@comment` directive

Outputs HTML comments only when `fuse-utilities.blade.render_comments` is true (env `RENDER_BLADE_COMMENTS`,
default: every environment except `production`).

```blade
@comment('Hero block: '.$block->type)            {{-- outputs <!-- Hero block: ... --> --}}

@comment
    <!-- any markup here is only output when comments are enabled -->
@endcomment
```

The single-argument form escapes the text and replaces `--`. Use it for debugging markers, never for content.

## Tailwind breakpoint badge

```blade
<x-fuse-utilities::tailwind />
```

Renders a fixed badge showing the current breakpoint and viewport width, only when `APP_ENV=local`. Requires
Alpine.js. Place it once, near the end of the layout's `<body>`.

## Dialog ids

Dialog ids must be unique per request. Register each id before rendering the dialog:

```php
$id = \MityDigital\FuseUtilities\Support\Dialog::registerId($id);
```

It returns the id, and throws `MityDigital\FuseUtilities\Exceptions\DialogException` when the id is empty, not a
string, or already used in this request. When the same dialog component can render more than once on a page
(e.g. inside a repeated block), build the id from something unique such as the block or entry id.

## Theme

`MityDigital\FuseUtilities\Support\Theme` is a base class. The Starter Kit extends it as `App\Support\Theme` and
registers that subclass as a scoped (per-request) service. Always resolve and type-hint `App\Support\Theme`:
resolving the package class returns a different instance with separate state. Add project behaviour to the
subclass, never to the package class. The project may add its own methods; read the subclass and any project
guidelines before using it.

Methods provided by the base class:

| Method | Purpose |
|---|---|
| `setBlock($block)` | Marks the page builder block being rendered and shares it with views as `$block`. |
| `getBlock()`, `hasBlock()` | The current block, and whether one is set. |
| `clearBlock()` | Clears the current block, remembering its theme as the last theme. |
| `getLastTheme()` | Theme of the previously rendered block (e.g. for spacing between blocks with the same theme). |
| `block()` | The shared block as `Statamic\Fields\Values` (empty when none). |
| `settings()` | The block's `settings` values (empty `Values` when none). |
| `getTheme()` | The block's `settings.theme` value, or `null`. |
| `setEntry($entry)` | Shares the entry with views as `$entry`. |

Call `setBlock()` before rendering each block and `clearBlock()` after it. Do not call the deprecated `setTheme()`:
it has no effect. To use a different theme for one call, pass it to the subclass method instead (most accept an
optional `$theme`).

## Validation rules

All in `MityDigital\FuseUtilities\Rules`. They accept a plain string or a Statamic `code` field value
(`['code' => ...]`), and pass when the value is blank, so combine them with `required` if needed.

| Rule | Passes when |
|---|---|
| `IsJsonLdRule` | Valid JSON, with no `<script` tag. |
| `IsCodeFieldtypeHtmlRule` | Contains well-formed HTML elements or comments (plain text fails). |
| `IsTailwindLikeRule('integer')` | Space-separated tokens like `350 md:600`. |
| `IsTailwindLikeRule('ratio')` | Space-separated tokens like `16/9 lg:3/1`. |

In blueprint or fieldset YAML (single-quoted, with `''` for a quote inside):

```yaml
validate:
  - 'new MityDigital\FuseUtilities\Rules\IsJsonLdRule()'
  - 'new MityDigital\FuseUtilities\Rules\IsTailwindLikeRule(''ratio'')'
```

`IsTailwindLikeRule` only checks syntax; it does not check that breakpoint names exist in the image config.

## Control panel field conditions

The package registers these custom conditions for blueprint `if` / `unless`:

| Condition | True when the target field's value |
|---|---|
| `hasMultiple` / `doesNotHaveMultiple` | Has more than one item / one item or fewer. |
| `not_contains` | Contains none of the given parameters (or is empty). |
| `requiresLabel` / `doesNotRequireLabel` | Is a link that is not / is an `asset::`, `entry::` or `@` reference. |

```yaml
if:
  images: 'custom hasMultiple'
```

```yaml
if:
  link: 'custom requiresLabel'
```

## `settings_features` fieldtype

A hidden fieldtype whose value is never saved. Do not use it to store data.
