# Responsive images

## Project wrappers

- Check `resources/views/components/` for a project component that wraps the package one (for example an
  `image.blade.php` that renders `<x-fuse-utilities::image>`, used as `<x-image>`). If there is one, use it in
  templates.
- Use `<x-fuse-utilities::image>` or `Image::make()` directly only when the wrapper can't produce the markup you
  need.
- The breakpoint syntax, configuration and behaviour below still apply to `width` and `aspect-ratio` values passed
  through a wrapper.
- A wrapper may not forward every prop (for example `quality`, `fit`, `sizes`, `alt` or `picture-class`). Read its
  `@props` before relying on one.

## Blade component

```blade
<x-fuse-utilities::image
    :asset="$block->image"
    width="350 md:600 lg:900"
    aspect-ratio="3/2 md:1/1 lg:4/5"
    class="h-auto w-full object-cover"
/>
```

`asset` must be a Statamic `Statamic\Contracts\Assets\Asset`. For an `assets` field with `max_files: 1`, pass the
field value (it augments to a single asset); for multiple assets, loop over them.

| Prop | Default | Notes |
|---|---|---|
| `asset` | required | The asset to render. |
| `width` | `null` | Pixel widths per breakpoint, e.g. `350 md:600`. |
| `aspect-ratio` | `null` | `width/height` per breakpoint, e.g. `16/9 lg:3/1`. Defaults to the asset's own ratio. |
| `quality` | `null` | Glide quality per breakpoint, e.g. `80 lg:70`. Defaults to Glide's setting. |
| `fit` | `crop_focal` | Glide fit. `crop_focal` crops around the asset's focal point. |
| `sizes` | `null` | Replaces the generated `sizes` attribute. Use for fluid layouts, e.g. `sizes="100vw"`. |
| `alt` | asset `alt` | Overrides the asset's alt text. Pass `alt=""` for decorative images. |
| `class` | `null` | Classes on the `<img>`. |
| `picture-class` | `null` | Classes on the `<picture>`. |
| `style` | `null` | Inline style on the `<img>`. |
| `loading` | `lazy` | Use `loading="eager"` for above-the-fold or LCP images. |
| `decoding` | `async` | |

## Breakpoint syntax

- Values are space-separated tokens. A token without a prefix is the base (mobile) value; `md:600` applies from the
  `md` breakpoint upwards. Values carry forward to larger breakpoints until overridden.
- Allowed prefixes are the keys of `fuse-utilities.image.breakpoints` (default `sm`, `md`, `lg`, `xl`, `2xl`).
- Widths and quality must be positive integers. Ratios must be `number/number` with both parts above zero.
- Invalid tokens or unknown breakpoints throw `InvalidArgumentException` at render time. Validate author-entered
  values in blueprints with `IsTailwindLikeRule` (see [blade-cp-and-validation.md](blade-cp-and-validation.md)).

## Behaviour to rely on

- Output is a `<picture>` with WebP sources plus the original format, and an `<img>` fallback with `width` and
  `height` set.
- Each width is rendered at the configured densities (default 1x and 2x) and is never larger than the asset itself.
- When `width` is omitted, widths come from `default_width` (350) for the base, then each breakpoint's pixel value,
  capped at the asset width.
- When `width` has no base value (e.g. `width="md:600"`), the original image is used below `md`.
- SVG, GIF and other non-raster files, or assets without dimensions, are output as the original URL with no
  manipulation.

## Configuration (`config/fuse-utilities.php`)

```php
'image' => [
    'default_width' => 350,
    'breakpoints' => ['sm' => 640, 'md' => 768, 'lg' => 1024, 'xl' => 1280, '2xl' => 1536],
    'default_fit' => 'crop_focal',
    'densities' => [1, 2],
],
```

Keep `breakpoints` aligned with the project's Tailwind breakpoints.

## Custom markup with `Image::make()`

When the component's markup does not fit (for example a CSS background), build the data yourself:

```php
use MityDigital\FuseUtilities\Support\Image;

$image = Image::make($asset, [
    'width' => '350 md:600',
    'aspect_ratio' => '16/9',   // snake_case keys here
    'quality' => null,
    'fit' => 'crop_focal',
    'sizes' => null,
    'densities' => [1, 2],      // only settable here, not on the component
]);
```

It returns `MityDigital\FuseUtilities\Data\ImageData`:

- `fallback` (URL), `fallback_srcset`, `sizes`, `alt`, `width`, `height`
- `formats`: `['webp' => 'image/webp', 'jpg' => 'image/jpeg']`
- `sources`: `ImageSourceData[]`, largest breakpoint first, each with `media`, `sizes` and
  `srcset[$format]` as a list of `['url' => ..., 'descriptor' => '700w']`

## Private or protected images

Every URL is built by the container binding for `MityDigital\FuseUtilities\Contracts\ImageUrlGenerator`
(default `Support\StatamicImageUrlGenerator`). To route a private container through a signed or access-checked
route, implement the contract and bind it in the app's service provider:

```php
use MityDigital\FuseUtilities\Contracts\ImageUrlGenerator;
use MityDigital\FuseUtilities\Support\StatamicImageUrlGenerator;
use Statamic\Contracts\Assets\Asset;

class ProjectImageUrlGenerator implements ImageUrlGenerator
{
    public function __construct(private StatamicImageUrlGenerator $default) {}

    public function generate(Asset $asset, array $params = []): string
    {
        if ($asset->container()->handle() !== 'private') {
            return $this->default->generate($asset, $params);
        }

        return url()->temporarySignedRoute('private.asset.image', now()->addHours(2), [
            'asset' => \Statamic\Support\Str::toBase64Url($asset->id()),
            ...$params,
        ]);
    }
}

// AppServiceProvider::register()
$this->app->bind(ImageUrlGenerator::class, ProjectImageUrlGenerator::class);
```

- An empty `$params` array means "the original file"; otherwise `$params` holds Glide parameters
  (`w`, `h`, `fit`, `fm`, `q`).
- The route's controller must check access, then serve the original or pass the validated parameters to Statamic's
  image generator. Never trust `$params` from the request without validating them.
