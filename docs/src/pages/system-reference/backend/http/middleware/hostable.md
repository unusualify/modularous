---
sidebarPos: 6
sidebarTitle: HostableMiddleware
---

# HostableMiddleware

**File**: `src/Http/Middleware/HostableMiddleware.php`  
**Alias**: `hostable`

Stub middleware reserved for host-based routing features. Currently passes all requests through unchanged.

## Current Behaviour

```php
public function handle($request, Closure $next)
{
    return $next($request);
}
```

## Intended Use

The `hostable` alias is an optional middleware that can be applied to routes that should only be accessible from specific hosts or subdomains. Apply it selectively to routes that need host-based restrictions:

```php
Route::middleware('hostable')->group(function () {
    Route::get('/tenant-dashboard', TenantController::class);
});
```

## Notes

- Unlike the core middleware group, `hostable` is **not** applied automatically. It must be added explicitly to routes that need it.
- The `HostRouteRegistrar` in `src/Support/` works in conjunction with this middleware for multi-tenant subdomain routing scenarios.
