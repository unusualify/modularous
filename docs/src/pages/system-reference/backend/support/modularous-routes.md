---
sidebarPos: 9
sidebarTitle: ModularousRoutes
---

# ModularousRoutes

`Unusualify\Modularous\Support\ModularousRoutes`

Centralises all Modularous route configuration: the admin group options (name prefix, domain/prefix), middleware alias registration, and middleware group definitions. Resolved from the service container by the route service provider.

## Methods

### `configureRoutePatterns(): void`

Reads `modularous.route_patterns` from config and calls `Route::pattern()` for each entry. Run once at boot to constrain route parameter formats (e.g. `{id}` to digits only).

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
| `webMiddlewares()` | `web`, `modularous.log`, `modularous.core` |
| `webPanelMiddlewares()` | `web.auth`, `modularous.log`, `modularous.core`, `modularous.panel` |
| `apiMiddlewares()` | `api`, `modularous.log`, `modularous.core` |
| `apiPanelMiddlewares()` | `api.auth`, `modularous.log`, `modularous.core`, `modularous.panel` |
| `defaultMiddlewares()` | `modularous.log`, `modularous.core` |
| `defaultPanelMiddlewares()` | `modularous.panel` |

### `generateRouteMiddlewares(): void`

Registers all Modularous middleware aliases and groups with the Laravel router:

**Aliases registered:**

| Alias | Middleware Class |
|-------|-----------------|
| `modularous.auth` | `AuthenticateMiddleware` |
| `modularous.guest` | `RedirectIfAuthenticatedMiddleware` |
| `modularous.utm` | `UtmMiddleware` |
| `modularous.log` | `LogMiddleware` |
| `modularous.language` | `LanguageMiddleware` |
| `modularous.impersonate` | `ImpersonateMiddleware` |
| `modularous.loadLocalizedConfig` | `LoadLocalizedConfig` |
| `modularous.navigation` | `NavigationMiddleware` |
| `authorization` | `AuthorizationMiddleware` |
| `modularous.company.registration` | `CompanyRegistrationMiddleware` |
| `modularous.redirector` | `RedirectorMiddleware` |
| `hostable` | `HostableMiddleware` |

**Groups registered:**

| Group | Members |
|-------|---------|
| `web.auth` | `web`, `modularous.auth:{guard}` |
| `api.auth` | `api`, `throttle:api`, `modularous.auth:{guard}` |
| `modularous.core` | `modularous.utm`, `modularous.impersonate`, `modularous.language`, `modularous.loadLocalizedConfig`, `modularous.navigation`, `inertia.middleware` |
| `modularous.panel` | `authorization`, `modularous.company.registration`, `modularous.redirector` |

Spatie Permission middleware (`role`, `permission`, `role_or_permission`) aliases are also registered here.

## Related

- [Middleware](/system-reference/backend/http/middleware/overview) — full middleware class reference
- [HostRouting](./host-routing) — uses `hostable` alias registered here
