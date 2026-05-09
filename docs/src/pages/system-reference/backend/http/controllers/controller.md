---
sidebarPos: 6
sidebarTitle: Controller
---

# Controller

**File**: `src/Http/Controllers/Controller.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers`  
**Extends**: `Illuminate\Routing\Controller`  
**Traits**: `AuthorizesRequests`, `DispatchesJobs`, `ValidatesRequests`

Root controller for the Modularous package. Provides two behaviours on top of Laravel's base: optional custom exception handler binding and a middleware removal utility.

## Constructor

```php
public function __construct()
```

When `modularous.bind_exception_handler` is `true` in the application config, binds Modularous's own exception handler into the container so that API and admin-panel errors are formatted consistently.

## Methods

### `removeMiddleware`

```php
protected function removeMiddleware(string $middleware): void
```

Removes a named middleware from the controller's middleware stack. Iterates the internal `$middleware` array and unsets any entry whose `middleware` key matches `$middleware`.

Used by sub-controllers (e.g. `PanelController`, `ProfileController`) to opt specific actions out of inherited middleware without rewriting the full stack.

## Inheritance

All Modularous controllers extend this class either directly or via `CoreController` → `PanelController` → `BaseController`. Auth controllers extend it through `Auth\Controller`.
