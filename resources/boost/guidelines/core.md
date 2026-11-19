## Fuse Utilities

- This application uses `mitydigital/fuse-utilities`. Use the `fuse-utilities` skill for its helpers.
- Render content images through the project's image component when it has one that wraps `<x-fuse-utilities::image>` (the Fuse Starter Kit's is `<x-image>`); otherwise use `<x-fuse-utilities::image>`. Never hand-write `<picture>`, `srcset` or Glide tags.
- Use the project's `App\Support\Theme` class, never `MityDigital\FuseUtilities\Support\Theme` directly.
