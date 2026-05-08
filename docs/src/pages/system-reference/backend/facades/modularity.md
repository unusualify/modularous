---
sidebarPos: 8
sidebarTitle: Modularity
---

# Modularity

**Facade**: `Unusualify\Modularity\Facades\Modularity`  
**Accessor**: `modularity`  
**Underlying**: `Unusualify\Modularity\Modularity`

The primary facade for interacting with the Modularous module registry. Used throughout controllers, service providers, and helpers to resolve modules, check existence, and manage module state.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getModules` | `(): array` | Returns all registered modules |
| `getEnabledModules` | `(): array` | Returns only enabled modules |
| `hasModule` | `(string $name): bool` | Checks if a module is registered |
| `find` | `(string $name): Module` | Finds a module by name, returns `null` if not found |
| `findOrFail` | `(string $name): Module` | Finds a module, throws if not found |
| `getModulePath` | `(string $moduleName): string` | Returns the filesystem path to a module |
| `assetPath` | `(string $module): string` | Returns the public asset path for a module |
| `moduleAsset` | `(string $module, string $asset): string` | Returns the full URL for a module asset |
| `enableModule` | `(string $moduleName): void` | Enables a module |
| `disableModule` | `(string $moduleName): void` | Disables a module |
| `deleteModule` | `(string $moduleName): void` | Removes a module |

## Usage

```php
use Unusualify\Modularity\Facades\Modularity;

// Find a module and call methods on it
$module = Modularity::find('Blog');
$routeUrl = $module->getRouteActionUrl('posts', 'index');

// Check availability
if (Modularity::hasModule('Payment')) {
    // payment features available
}

// Iterate all enabled modules
foreach (Modularity::getEnabledModules() as $module) {
    echo $module->getName();
}
```

## Notes

- `find()` is the most-used method across the codebase, also available as the global `curtModule()` helper.
- Module instances returned are `Nwidart\Modules\Module` objects extended by Modularous.
