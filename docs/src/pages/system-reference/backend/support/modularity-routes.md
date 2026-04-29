---
sidebarPos: 9
sidebarTitle: ModularityRoutes
---

# ModularityRoutes

`Unusualify\Modularity\Support\ModularityRoutes`

Centralises all Modularous route configuration: the admin group options (name prefix, domain/prefix), middleware alias registration, and middleware group definitions. Resolved from the service container by the route service provider.

## Methods

### `configureRoutePatterns(): void`

Reads `modularity.route_patterns` from config and calls `Route::pattern()` for each entry. Run once at boot to constrain route parameter formats (e.g. `{id}` to digits only).

### `groupOptions(): array`

Returns the options array passed to the top-level admin `Route::group()`:

```php
[
    'as'     => 'admin.',          // route name prefix
    'domain' => 'admin.app.test',  // when admin URL is separate
    // OR
    'prefix' => 'admin',           // when admin is a path prefix
    'domain' => 'app.test',
]
```

### Middleware Stack Methods

| Method | Middlewares Included |
|--------|---------------------|
| `webMiddlewares()` | `web`, `modularity.log`, `modularity.core` |
| `webPanelMiddlewares()` | `web.auth`, `modularity.log`, `modularity.core`, `modularity.panel` |
| `apiMiddlewares()` | `api`, `modularity.log`, `modularity.core` |
| `apiPanelMiddlewares()` | `api.auth`, `modularity.log`, `modularity.core`, `modularity.panel` |
| `defaultMiddlewares()` | `modularity.log`, `modularity.core` |
| `defaultPanelMiddlewares()` | `modularity.panel` |

### `generateRouteMiddlewares(): void`

Registers all Modularous middleware aliases and groups with the Laravel router:

**Aliases registered:**

| Alias | Middleware Class |
|-------|-----------------|
| `modularity.auth` | `AuthenticateMiddleware` |
| `modularity.guest` | `RedirectIfAuthenticatedMiddleware` |
| `modularity.utm` | `UtmMiddleware` |
| `modularity.log` | `LogMiddleware` |
| `modularity.language` | `LanguageMiddleware` |
| `modularity.impersonate` | `ImpersonateMiddleware` |
| `modularity.loadLocalizedConfig` | `LoadLocalizedConfig` |
| `modularity.navigation` | `NavigationMiddleware` |
| `authorization` | `AuthorizationMiddleware` |
| `modularity.company.registration` | `CompanyRegistrationMiddleware` |
| `modularity.redirector` | `RedirectorMiddleware` |
| `hostable` | `HostableMiddleware` |

**Groups registered:**

| Group | Members |
|-------|---------|
| `web.auth` | `web`, `modularity.auth:{guard}` |
| `api.auth` | `api`, `throttle:api`, `modularity.auth:{guard}` |
| `modularity.core` | `modularity.utm`, `modularity.impersonate`, `modularity.language`, `modularity.loadLocalizedConfig`, `modularity.navigation`, `inertia.middleware` |
| `modularity.panel` | `authorization`, `modularity.company.registration`, `modularity.redirector` |

Spatie Permission middleware (`role`, `permission`, `role_or_permission`) aliases are also registered here.

## Related

- [Middleware](/system-reference/backend/http/middleware/overview) — full middleware class reference
- [HostRouting](./host-routing) — uses `hostable` alias registered here
