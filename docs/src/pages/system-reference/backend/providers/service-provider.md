---
sidebarPos: 8
sidebarTitle: ServiceProvider
---

# ServiceProvider

**Class**: `Unusualify\Modularous\Providers\ServiceProvider`  
**Source**: `src/Providers/ServiceProvider.php`  
**Extends**: `Illuminate\Support\ServiceProvider`

Abstract base class that all Modularous service providers extend. Sets the `$baseKey` shared across all providers and overrides two Laravel methods with Modularous-aware implementations.

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$baseName` | `string` | Human-readable package name, sourced from `MODULAROUS_BASE_NAME` env var (default: `'Modularous'`) |
| `$baseKey` | `string` | Snake-case version of `$baseName` used as the config namespace key (e.g. `modularous`) |
| `$terminalNamespace` | `string` | Root namespace for console commands: `Unusualify\Modularous\Console` |
| `$viewSourcePath` | `string` | Absolute path to `resources/views` inside the package |

## Constructor

```php
public function __construct($app)
```

Reads `MODULAROUS_BASE_NAME` from the environment and derives `$baseKey` via `Str::snake()`. Both values are available to every extending provider.

## Overridden Methods

### `mergeConfigFrom(string $path, string $key)`

Replaces Laravel's default `array_merge` with `array_merge_recursive_preserve()`. This ensures that deeply nested config keys set by the application are **not** overwritten by the package defaults — application config always wins at every level of nesting.

### `getPublishableViewPaths(): array`

Scans every path in `config('view.paths')` for a `modules/{baseKey}` subdirectory. Returns only the paths that exist, allowing the application to publish and override package views while the package views serve as fallback.

## Usage

Never register `ServiceProvider` directly. Extend it when creating a new internal provider:

```php
class MyProvider extends \Unusualify\Modularous\Providers\ServiceProvider
{
    public function boot(): void
    {
        // $this->baseKey is available here
        $this->loadViewsFrom(
            array_merge(
                $this->getPublishableViewPaths(),
                [__DIR__ . '/../../resources/views']
            ),
            $this->baseKey
        );
    }
}
```
