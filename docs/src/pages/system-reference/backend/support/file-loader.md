---
sidebarPos: 5
sidebarTitle: FileLoader
---

# FileLoader

`Unusualify\Modularous\Support\FileLoader`

Extends Laravel's `Illuminate\Translation\FileLoader` to support multiple translation search paths and expose runtime path/group introspection. It is bound in the service container and powers Modularous multi-module translation resolution.

## Extended API

| Method | Return | Description |
|--------|--------|-------------|
| `getPaths(): array` | `string[]` | Return all registered translation root directories |
| `getGroups(): array` | `string[]` | Scan all paths recursively and return unique translation group names (PHP file basenames) |
| `addPath(array\|string $path)` | `void` | Append one or more additional search paths at runtime |

The constructor signature mirrors Laravel's own loader:

```php
new FileLoader(Filesystem $files, array|string $path)
```

## How It Works

`getGroups()` performs a recursive filesystem scan — for every `.php` file found under any registered path, it strips the `.php` extension and collects unique group names. This lets Modularous enumerate all available translation keys without hard-coding file names.

## Example

```php
/** @var \Unusualify\Modularous\Support\FileLoader $loader */
$loader = app('translation.loader');

// Add a module's translation path at runtime
$loader->addPath(module_path('Blog', 'Resources/lang'));

// List all registered translation groups
$groups = $loader->getGroups();
// ['validation', 'auth', 'pagination', 'blog', ...]
```

## Related

- [Translation service](/system-reference/backend/services/translation) — uses `FileLoader` to sync translation files
