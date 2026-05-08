---
sidebarPos: 12
sidebarTitle: ModularityRoutes
---

# ModularityRoutes

**Facade**: `Unusualify\Modularity\Facades\ModularityRoutes`  
**Accessor**: `Unusualify\Modularity\Support\ModularityRoutes::class`  
**Underlying**: `Unusualify\Modularity\Support\ModularityRoutes`

Handles route middleware registration and provides the standard middleware stacks for web and API routes. Used in `BaseServiceProvider` to register all Modularous middleware aliases and groups.

## Key Responsibilities

- Registers all `modularity.*` middleware aliases via `generateRouteMiddlewares()`
- Defines the `modularity.core`, `modularity.panel`, `web.auth`, and `api.auth` middleware groups
- Provides helper methods that return pre-built middleware arrays for each route type

## Middleware Stacks

| Method | Stack |
|--------|-------|
| `webMiddlewares()` | `modularity.log` + `modularity.core` |
| `webPanelMiddlewares()` | `modularity.log` + `modularity.core` + `modularity.panel` |
| `apiMiddlewares()` | `modularity.log` + `api.auth` |
| `apiPanelMiddlewares()` | `modularity.log` + `api.auth` + `modularity.panel` |

## Usage

```php
use Unusualify\Modularity\Facades\ModularityRoutes;

// In a route file
Route::middleware(ModularityRoutes::webPanelMiddlewares())
    ->prefix(adminUrlPrefix())
    ->group(function () {
        // panel routes
    });
```

## Notes

- `generateRouteMiddlewares()` is called automatically during `BaseServiceProvider::boot()`. You do not need to call it manually.
- All 14 Modularous middleware classes are wired to their aliases here. See [Middleware](/system-reference/backend/http/middleware/overview) for full details.
