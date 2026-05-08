---
sidebarPos: 10
sidebarTitle: ModularityVite
---

# ModularityVite

`Unusualify\Modularity\Support\ModularityVite`

Extends Laravel's `Illuminate\Foundation\Vite` to point at Modularous own asset manifest instead of the host application's default `public/build/manifest.json`. Used internally by the `@modularityVite` Blade directive and the `Assets` service.

## Defaults

| Property | Value |
|----------|-------|
| `$buildDirectory` | `vendor/modularity` |
| `$manifestFilename` | `modularity-manifest.json` |
| `$integrityKey` | `integrity` |

The resolved asset path is therefore `public/vendor/modularity/<hashed-file>`.

## Behaviour

When the Vite dev server is running (`hot` file present), `__invoke()` injects the standard `@vite/client` and `@vite-plugin-svg-spritemap/client` scripts followed by each entrypoint as a hot-reload module tag.

In production mode, `__invoke()` reads `modularity-manifest.json`, emits `<link rel="modulepreload">` tags for all chunks, and then the actual `<script type="module">` and `<link rel="stylesheet">` tags for each entrypoint.

## Usage

Consume via the `Assets` service or the `@modularityVite` directive — do not instantiate directly:

```blade
{{-- In your layout --}}
@modularityVite(['resources/js/app.js', 'resources/css/app.css'])
```

Or in PHP:

```php
use Unusualify\Modularity\Services\Assets;

echo app(Assets::class)->vite(['resources/js/app.js']);
```

## Related

- [Assets service](/system-reference/backend/services/assets) — higher-level facade over `ModularityVite`
- [Assets commands](/guide/console/assets/overview) — `modularity:assets:build` / `modularity:assets:dev`
