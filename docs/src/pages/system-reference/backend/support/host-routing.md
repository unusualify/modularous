---
sidebarPos: 7
sidebarTitle: HostRouting
---

# HostRouting & HostRouteRegistrar

These two classes implement Modularous **multi-tenant host-based routing** — the ability to scope route groups by the incoming request's `Host` header, automatically resolving the active tenant model.

Both classes live in `Unusualify\Modularity\Support` and are constructed with the application instance and a base host name.

---

## HostRouting

`Unusualify\Modularity\Support\HostRouting`

The primary class. It holds the active tenant model, builds the `Route::group()` options (domain, prefix, middleware), and exposes methods for registering host-aware route groups.

### Constructor

```php
new HostRouting(
    Application $app,
    string $baseHostName,
    array $hostableClasses = [],
    array $options = [],
)
```

### Key Methods

| Method | Description |
|--------|-------------|
| `group(callable $callback)` | Register a `Route::group` with the resolved domain/prefix/middleware options |
| `setModel(array\|string $model)` | Set the hostable model class(es) and re-resolve the active tenant |
| `setOptions(array $options)` | Merge domain/prefix/middleware options; accepts `model` and `middleware` keys |
| `getHostModel()` | Return the resolved tenant model instance (or `null` if no match) |
| `getBaseHostName(): string` | Return the fallback host name |
| `getRouteArguments(): array` | Merge tenant `hostableRouteArguments()` with current route parameters |
| `getRouteParameters(): array` | Return the raw route parameters for the current request |
| `combineHostModels(): Collection` | Fetch all records from the hostable model classes |
| `classesIsHostable(): bool` | Return `true` if all hostable model tables exist in the database |

### How the Tenant Is Resolved

On construction (and on `setModel()`), `setHostModel()` calls `combineHostModels()` to load all records from the given model class(es) and picks the one whose `url` attribute matches `$request->getHost()`. If no match is found, `getHostModel()` returns `null` and the base host name is used as the domain.

### Usage

```php
// In a route file:
$hostRouting = app(HostRouting::class, [
    'baseHostName' => config('app.url'),
    'hostableClasses' => [Tenant::class],
]);

$hostRouting->setModel(Tenant::class)->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

---

## HostRouteRegistrar

`Unusualify\Modularity\Support\HostRouteRegistrar`

An older, more opaque variant used internally by Modularous own route files. It exposes only two fluent callables (`host()` and `group()`) and a set of `allowedAttributes` (`middleware`, `name`). Prefer `HostRouting` for new code.

### Key Difference

`HostRouteRegistrar` builds group options lazily via a `__call` proxy. `group()` must always be called last after chaining attribute setters.

```php
$registrar->host([Tenant::class])->group(function () {
    // routes
});
```

---

## Route Group Options Produced

Regardless of which class is used, the resolved options follow this shape:

| Key | Tenant found | No tenant |
|-----|-------------|-----------|
| `domain` | `$tenant->url` | `$baseHostName` |
| `prefix` | Tenant's `hostableChildRouteParameters()` joined with `/` | Model's `hostableRouteBindingParameter()` per class |
| `middleware` | `['hostable']` (+ any extras) | same |

## Related

- [ModularityRoutes](./modularity-routes) — registers the `hostable` middleware alias
- [`HasHostable` trait](/system-reference/backend/entity-traits/overview) — models must implement `hostables()`, `hostableRouteArguments()`, etc.
