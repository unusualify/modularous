---
sidebarPos: 3
sidebarTitle: BaseServiceProvider
---

# BaseServiceProvider

**Class**: `Unusualify\Modularity\Providers\BaseServiceProvider`  
**Source**: `src/Providers/BaseServiceProvider.php`  
**Extends**: [`ServiceProvider`](./service-provider)

The core provider that handles all container bindings, macro registration, view composers, log channels, and the application scheduler. It is the heaviest provider in the stack and is registered by [`ModularityProvider`](./modularity-provider).

## `register()`

### Helper files

All PHP files in `src/Helpers/` are required in sequence so global helper functions are available globally.

### Config merging

| Source | Merged into |
|--------|------------|
| `config/config.php` | `modularity.*` |
| `config/merges/*.php` | `modularity.{filename}.*` |
| `config/disks.php` | `filesystems.disks.*` |

### Container bindings (singletons)

| Binding key | Class | Description |
|-------------|-------|-------------|
| `RepositoryInterface` | `Modularity` | Overrides nwidart's `LaravelFileRepository` with the Modularity-extended implementation |
| `modularity` | alias for `Modularity` | Convenience alias |
| `modularity.navigation` | `ModularityNavigation` | Admin navigation service |
| `model.relation.namespace` | — | Eloquent relations namespace string |
| `model.relation.pattern` | — | Regex pattern derived from relation namespace |
| `unusualify.hosting` | `HostRouting` | Host-based routing helper |
| `unusualify.hostRouting` | `HostRouteRegistrar` | Host-based route registrar |
| `Filepond` | `FilepondManager` | Filepond upload manager |
| `currency.exchange` | `CurrencyExchangeService` | Currency exchange rates |
| `CurrencyProviderInterface` | Resolved at runtime | Uses `modularity.currency_provider` config; falls back to `SystemPricingCurrencyProvider` then `NullCurrencyProvider` |
| `modularity.relationship.graph` | `CacheRelationshipGraph` | Relationship dependency graph for cache invalidation |
| `modularity.cache` | `ModularityCacheService` | Package-level cache service |
| `migration.backup` | `MigrationBackup` | Migration backup utility |
| `modularity.redirect` | `RedirectService` | Redirect management |
| `modularity.utm` | `UtmParameters` | UTM parameter tracking |
| `auth.register` | `RegisterBrokerManager` | Auth registration broker |
| `inertia.middleware` | `HandleInertiaRequests` | Inertia request handler |

### Aliases

| Alias | Class |
|-------|-------|
| `ModularityVite` | `Facades\ModularityVite` |
| `GeoIP` | `Torann\GeoIP\Facades\GeoIP` |

### Translation service

Extends Laravel's `translation.loader` with a multi-path `FileLoader` that searches:
1. `vendor/laravel/framework/.../Translation/lang`
2. Package `lang/`
3. Application `lang/` (when not overridden by `modularity/lang/`)
4. `app['path.lang']`

Extends `translator` with a custom `Translator` instance that reads from these paths in order.

## `boot()`

### Auth config validation

When `enabled.users-management` is on and not running in console, validates that the Modularity auth guard, provider, and password config all exist in `config/auth`. Throws `AuthConfigurationException` with a descriptive message if any are missing.

### Media and file service singletons

| Condition | Binding |
|-----------|---------|
| `enabled.media-library` = true | `imageService` → class from `media_library.image_service` config |
| `enabled.file-library` = true | `fileService` → class from `file_library.file_service` config |

Local disk URL is auto-configured when endpoint type is `local` and disk matches the package default.

### Macros

| Macro | Target | Description |
|-------|--------|-------------|
| `Str::modularitySlug()` | `Illuminate\Support\Str` | Slug with locale-aware dictionary from `slug-dictionary` translations |
| `Collection::recursive()` | `Illuminate\Support\Collection` | Recursively converts all nested arrays/objects to Collections |
| `Request::getCachedUserCurrency()` | `Illuminate\Support\Facades\Request` | Returns user's currency from session or the default pricing currency |

### View composers

Registered on `'*'` (all views) or specific layout views:

| Composer | Views | Condition |
|----------|-------|-----------|
| Inline | `*` | Always — injects `BASE_KEY`, `MODULARITY_VIEW_NAMESPACE`, `SYSTEM_PACKAGE_VERSIONS` |
| [`Urls`](../http/view-composers/urls) | `*` | Always |
| [`CurrentUser`](../http/view-composers/current-user) | `admin.*`, `{baseKey}::*` | `enabled.users-management` = true |
| [`MediasUploaderConfig`](../http/view-composers/medias-uploader-config) | master/app-inertia layouts | `enabled.media-library` = true |
| [`FilesUploaderConfig`](../http/view-composers/files-uploader-config) | master/app-inertia layouts | `enabled.file-library` = true |
| [`Localization`](../http/view-composers/localization) | master/auth/app-inertia layouts | Always |
| Inline render flags | `admin.*`, `templates.*`, `{baseKey}::*` | Always — injects `renderForBlocks`, `renderForModal` |

### Scheduler

| Command | Schedule |
|---------|----------|
| `modularity:fileponds:scheduler --days=7` | Daily |
| `telescope:prune --hours=168` | Daily (appends to `logs/scheduler.log`) |
| `modularity:scheduler:chatable` | Every minute |

### Log channels

| Channel | Driver | Description |
|---------|--------|-------------|
| `modularity` | `monolog` with `ModularityLogHandler` | Package debug log; retention 14 days; level from `MODULARITY_LOG_LEVEL` env |
| `modularity-notification-failure` | `daily` | Notification failure log; `storage/logs/modularity-notification-failure.log`; 14-day retention |

### Password reset URL

Overrides Laravel's default reset URL to point to `admin.password.reset` named route.

### `php artisan about`

Adds a **Modularous** section to `php artisan about` output showing cache status, scan status, theme, URLs, vendor path, and version.
