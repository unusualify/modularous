---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Providers
---

# Providers

Modularous ships with seven service providers. They form a strict registration hierarchy — the application only needs to register one entry point (`ModularousProvider`) and all others are booted in order.

## Provider Hierarchy

```
ModularousProvider          ← register in config/app.php
├── GeoIPServiceProvider    (third-party)
├── TimezoneServiceProvider (third-party)
├── BaseServiceProvider     ← core bindings, macros, view composers
├── ModuleServiceProvider   ← per-module boot (migrations, routes, views, lang)
├── RouteServiceProvider    ← system & module routing, route macros
├── AuthServiceProvider     ← gates, Horizon auth, email verification
└── CoverageServiceProvider ← code coverage analysis service
```

`ServiceProvider` is the abstract base all internal providers extend. It is never registered directly.

## Provider Reference

| Provider | Source | Responsibility |
|----------|--------|----------------|
| [`ServiceProvider`](./service-provider) | `Providers/ServiceProvider.php` | Abstract base — `$baseKey`, config merge strategy, publishable view paths |
| [`ModularousProvider`](./modularous-provider) | `Providers/ModularousProvider.php` | Entry point — registers all other providers in order |
| [`BaseServiceProvider`](./base-service-provider) | `Providers/BaseServiceProvider.php` | Core bindings, commands, macros, view composers, log channels, scheduler |
| [`ModuleServiceProvider`](./module-service-provider) | `Providers/ModuleServiceProvider.php` | Boots every enabled nwidart module (migrations, views, lang, commands, providers) |
| [`RouteServiceProvider`](./route-service-provider) | `Providers/RouteServiceProvider.php` | Registers system routes, module routes, and route macros |
| [`AuthServiceProvider`](./auth-service-provider) | `Providers/AuthServiceProvider.php` | Gates, Horizon access, custom email-verification URL/mail |
| [`TelescopeServiceProvider`](./telescope-service-provider) | `Providers/TelescopeServiceProvider.php` | Telescope access control and entry filtering |
| [`CoverageServiceProvider`](./coverage-service-provider) | `Providers/CoverageServiceProvider.php` | Code-coverage analysis bindings and commands |

## Registration

Add only the entry-point provider to your application:

```php
// config/app.php
'providers' => [
    // ...
    Unusualify\Modularous\Providers\ModularousProvider::class,
],
```
