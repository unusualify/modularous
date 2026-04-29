---
sidebarPos: 5
sidebarTitle: ModularityProvider
---

# ModularityProvider

**Class**: `Unusualify\Modularity\Providers\ModularityProvider`  
**Source**: `src/Providers/ModularityProvider.php`  
**Extends**: [`ServiceProvider`](./service-provider)

The single entry point for the entire Modularity package. Registers all internal and third-party providers in the correct order. Only this provider needs to be added to `config/app.php`.

## Provider Boot Order

```php
protected $providers = [
    // Third-party
    GeoIPServiceProvider::class,
    TimezoneServiceProvider::class,

    // Modularity internals
    BaseServiceProvider::class,
    ModuleServiceProvider::class,
    RouteServiceProvider::class,
    AuthServiceProvider::class,
    CoverageServiceProvider::class,
];
```

| Order | Provider | Purpose |
|-------|----------|---------|
| 1 | `GeoIPServiceProvider` | IP geolocation via `torann/geoip` |
| 2 | `TimezoneServiceProvider` | Timezone support via `camroncade/timezone` |
| 3 | [`BaseServiceProvider`](./base-service-provider) | Core bindings, commands, macros, view composers |
| 4 | [`ModuleServiceProvider`](./module-service-provider) | Per-module boot (migrations, views, lang, commands) |
| 5 | [`RouteServiceProvider`](./route-service-provider) | System and module routing |
| 6 | [`AuthServiceProvider`](./auth-service-provider) | Gates, Horizon auth, email verification |
| 7 | [`CoverageServiceProvider`](./coverage-service-provider) | Code-coverage analysis bindings |

## Methods

### `register()`

Iterates `$providers` and calls `$this->app->register($provider)` for each. No direct bindings are made here — all container registrations are delegated to the individual providers.

### `boot()`

Runs the `booted` callback when `exceptionalRunningInConsole()` is true (i.e., the application is running in a console context that should behave like a web request). Currently a no-op placeholder for post-boot console initialisation.

## Registration

```php
// config/app.php
'providers' => [
    Unusualify\Modularity\Providers\ModularityProvider::class,
],
```
