---
sidebarPos: 12
sidebarTitle: ModularousRoutes
---

# ModularousRoutes

**Facade**: `Unusualify\Modularous\Facades\ModularousRoutes`  
**Accessor**: `Unusualify\Modularous\Support\ModularousRoutes::class`  
**Underlying**: `Unusualify\Modularous\Support\ModularousRoutes`

Handles route middleware registration and provides the standard middleware stacks for web and API routes. Used in `BaseServiceProvider` to register all Modularous middleware aliases and groups.

## Key Responsibilities

- Registers all `modularous.*` middleware aliases via `generateRouteMiddlewares()`
- Defines the `modularous.core`, `modularous.panel`, `web.auth`, and `api.auth` middleware groups
- Provides helper methods that return pre-built middleware arrays for each route type

## Middleware Stacks

| Method | Stack |
|--------|-------|
| `webMiddlewares()` | `modularous.log` + `modularous.core` |
| `webPanelMiddlewares()` | `modularous.log` + `modularous.core` + `modularous.panel` |
| `apiMiddlewares()` | `modularous.log` + `api.auth` |
| `apiPanelMiddlewares()` | `modularous.log` + `api.auth` + `modularous.panel` |

## Usage

```php
use Unusualify\Modularous\Facades\ModularousRoutes;

// In a route file
Route::middleware(ModularousRoutes::webPanelMiddlewares())
    ->prefix(adminUrlPrefix())
    ->group(function () {
        // panel routes
    });
```

## Notes

- `generateRouteMiddlewares()` is called automatically during `BaseServiceProvider::boot()`. You do not need to call it manually.
- All 14 Modularous middleware classes are wired to their aliases here. See [Middleware](/system-reference/backend/http/middleware/overview) for full details.
