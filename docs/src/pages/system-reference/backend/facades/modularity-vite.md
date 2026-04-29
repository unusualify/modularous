---
sidebarPos: 13
sidebarTitle: ModularityVite
---

# ModularityVite

**Facade**: `Unusualify\Modularity\Facades\ModularityVite`  
**Accessor**: `Unusualify\Modularity\Support\ModularityVite::class`  
**Underlying**: `Unusualify\Modularity\Support\ModularityVite`

Modularous's Vite integration layer. Works like Laravel's built-in `Vite` facade but resolves assets from the Modularous package's build directory rather than the host application's `public/build`.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `__invoke` | `(string\|string[] $entrypoints, string\|null $buildDirectory = null): HtmlString` | Generates `<script>` and `<link>` tags for entrypoints |
| `hotAsset` | `(string $asset): string` | Returns the HMR dev server URL for an asset |
| `isRunningHot` | `(): bool` | Returns `true` if Vite dev server is active |
| `makeTagForChunk` | `(string $src, string $url, array\|null $chunk, array\|null $manifest): string` | Generates an HTML tag for a manifest chunk |
| `makePreloadTagForChunk` | `(string $src, string $url, array $chunk, array $manifest): string` | Generates a `<link rel="modulepreload">` tag |
| `chunk` | `(array $manifest, string $file): array` | Returns the manifest entry for a file |
| `manifest` | `(string $buildDirectory): array` | Returns the parsed Vite manifest JSON |
| `assetPath` | `(string $path): string` | Returns the public path for a built asset |
| `isCssPath` | `(string $path): bool` | Returns `true` if the path points to a CSS file |

## Usage

In Blade layouts:

```php
{!! ModularityVite::__invoke(['resources/js/app.js', 'resources/css/app.css']) !!}
```

Or via the `@modularityVite` Blade directive registered by the package:

```blade
@modularityVite(['resources/js/app.js'])
```

## Notes

- During development (`isRunningHot() === true`) assets are served from the Vite HMR server.
- In production, the manifest is read from the Modularous package's `public/build/manifest.json`.
