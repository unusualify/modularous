---
sidebarPos: 1
sidebarTitle: Overview
---

# Facades

Modularous registers 18 Laravel facades, all under the `Unusualify\Modularity\Facades\` namespace. Each facade is an alias to a bound service container entry, providing static-style access to the underlying service class.

## Overview

| Facade | Accessor | Underlying Service |
|--------|----------|--------------------|
| [Modularity](./modularity) | `modularity` | `Unusualify\Modularity\Modularity` |
| [ModularityCache](./modularity-cache) | `modularity.cache` | `ModularityCacheService` |
| [ModularityFinder](./modularity-finder) | `Finder::class` | `Support\Finder` |
| [ModularityLog](./modularity-log) | `modularity.log` | `Illuminate\Log\LogManager` (dedicated channel) |
| [ModularityRoutes](./modularity-routes) | `Support\ModularityRoutes::class` | `Support\ModularityRoutes` |
| [ModularityVite](./modularity-vite) | `Support\ModularityVite::class` | `Support\ModularityVite` |
| [Navigation](./navigation) | `modularity.navigation` | `ModularityNavigation` |
| [Redirect](./redirect) | `modularity.redirect` | `RedirectService` |
| [Utm](./utm) | `modularity.utm` | `UtmParameters` |
| [Filepond](./filepond) | `Filepond` | `Services\Filepond` / `FilepondManager` |
| [Coverage](./coverage) | `coverage.service` | `CoverageService` |
| [CurrencyExchange](./currency-exchange) | `currency.exchange` | `CurrencyExchangeService` |
| [MigrationBackup](./migration-backup) | `migration.backup` | `MigrationBackupService` |
| [RelationshipGraph](./relationship-graph) | `modularity.relationship.graph` | `CacheRelationshipGraph` |
| [Register](./register) | `auth.register` | `RegisterBrokerManager` |
| [HostRouting](./host-routing) | `unusualify.hosting` | `Support\HostRouting` |
| [HostRoutingRegistrar](./host-routing-registrar) | `unusualify.hostRouting` | `Support\HostRouting` |
| [UFinder](./u-finder) *(deprecated)* | `Finder::class` | `Support\Finder` |

## Usage Pattern

All Modularous facades follow standard Laravel facade usage:

```php
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\ModularityCache;

// Resolve a module
$module = Modularity::find('Blog');

// Cache a result scoped to a module route
$items = ModularityCache::remember($key, 3600, fn() => $repo->all(), 'Blog', 'posts');
```

Facades are registered in `BaseServiceProvider` and are available everywhere in the Laravel application after the service provider boots.
