# Commands, events and publishing

## `php artisan fuse:path-references {path=temp/}`

Lists every project file (tracked or untracked, respecting `.gitignore`) that contains the given path, with line
numbers. The default finds references to temporary assets in `temp/`. Use it before deleting or moving an asset
folder. Requires `git`; exits with failure when nothing matches.

```shell
php artisan fuse:path-references
php artisan fuse:path-references assets/old-folder/
```

## `php artisan fuse:generate-tailwind`

Reads `resources/tailwind/config.yaml` and overwrites `resources/tailwind/generated.html` with a file of complete
class names, so Tailwind generates classes that are built dynamically. Do not edit the generated file; change the
YAML and re-run the command.

```yaml
version: 4
colours:
  - primary
  - secondary
classes:
  - 'bg-[colour]'
  - 'text-[colour]-contrast'
standard:
  - 'grid-cols-2'
```

`version` must be `4`. Each class in `classes` is lower-cased and repeated with `[colour]` replaced by each colour.

## Collection mounts cannot be deleted in production

When `APP_ENV=production`, deleting an entry that is a collection's mount (e.g. the "Blog" page) throws an
exception. In local environments mounts can be deleted. Unmount the collection first if a mount really must go.

## Publishing

| Tag | Publishes to |
|---|---|
| `fuse-utilities-config` | `config/fuse-utilities.php` |
| `fuse-utilities-views` | `resources/views/vendor/fuse-utilities/` |
| `fuse-utilities-fieldsets` | `resources/fieldsets/vendor/fuse-utilities/` |
| `fuse-utilities-assets` | `public/vendor/fuse-utilities/` |

```shell
php artisan vendor:publish --tag=fuse-utilities-config
```

Only publish what you need to change: published copies no longer receive package updates.
