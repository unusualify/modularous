---
sidebarPos: 1
sidebarTitle: Overview
---

# Middleware

**Directory**: `src/Http/Middleware/`  
**Registration**: `src/Support/ModularityRoutes::generateRouteMiddlewares()`

Modularous registers its own middleware aliases and groups during the route bootstrapping phase. All aliases use the `modularity.*` prefix to avoid conflicts with application middleware.

## Middleware Aliases

| Alias | Class | Description |
|-------|-------|-------------|
| `modularity.auth` | [AuthenticateMiddleware](/system-reference/backend/http/middleware/authenticate) | Guards routes; stores intended URL; handles JSON 401 |
| `modularity.guest` | [RedirectIfAuthenticatedMiddleware](/system-reference/backend/http/middleware/redirect-if-authenticated) | Redirects already-authenticated users away from guest pages |
| `modularity.log` | [LogMiddleware](/system-reference/backend/http/middleware/log) | Injects `Request-Id` UUID into every request/response |
| `modularity.utm` | [UtmMiddleware](/system-reference/backend/http/middleware/utm) | Captures UTM params and shares them with views |
| `modularity.language` | [LanguageMiddleware](/system-reference/backend/http/middleware/language) | Resolves locale, timezone, and active currency per request |
| `modularity.impersonate` | [ImpersonateMiddleware](/system-reference/backend/http/middleware/impersonate) | Activates user impersonation from session |
| `modularity.loadLocalizedConfig` | [LoadLocalizedConfig](/system-reference/backend/http/middleware/load-localized-config) | Merges deferred and app-level config files at request time |
| `modularity.navigation` | [NavigationMiddleware](/system-reference/backend/http/middleware/navigation) | Shares navigation config with Blade layout views |
| `authorization` | [AuthorizationMiddleware](/system-reference/backend/http/middleware/authorization) | Shares profile/login shortcut schemas with the master layout |
| `modularity.company.registration` | [CompanyRegistrationMiddleware](/system-reference/backend/http/middleware/company-registration) | Guards routes that require a valid company (stub) |
| `modularity.redirector` | [RedirectorMiddleware](/system-reference/backend/http/middleware/redirector) | Consumes a pending redirect URL from `RedirectService` |
| `hostable` | [HostableMiddleware](/system-reference/backend/http/middleware/hostable) | Stub for host-based routing features |
| `inertia.middleware` | [HandleInertiaRequests](/system-reference/backend/http/middleware/handle-inertia-requests) | Inertia root view + shared props (auth, flash, config, store) |
| `role` | Spatie `RoleMiddleware` | Role-based route protection (Spatie Permission) |
| `permission` | Spatie `PermissionMiddleware` | Permission-based route protection (Spatie Permission) |
| `role_or_permission` | Spatie `RoleOrPermissionMiddleware` | Role OR permission route protection (Spatie Permission) |

## Middleware Groups

| Group | Middleware stack |
|-------|-----------------|
| `web.auth` | `web`, `modularity.auth:{guard}` |
| `api.auth` | `api`, `throttle:api`, `modularity.auth:{guard}` |
| `modularity.core` | `modularity.utm`, `modularity.impersonate`, `modularity.language`, `modularity.loadLocalizedConfig`, `modularity.navigation`, `inertia.middleware` |
| `modularity.panel` | `authorization`, `modularity.company.registration`, `modularity.redirector` |

## Route Stack by Type

Every route registered by Modularous belongs to one of four stacks:

| Route type | Middleware stack |
|------------|-----------------|
| `web` (public) | `web` + `modularity.log` + `modularity.core` |
| `webPanel` (authenticated admin) | `web.auth` + `modularity.log` + `modularity.core` + `modularity.panel` |
| `api` (public API) | `api` + `modularity.log` + `modularity.core` |
| `apiPanel` (authenticated API) | `api.auth` + `modularity.log` + `modularity.core` + `modularity.panel` |

## Request Flow

```
Incoming request
  └── modularity.log          ← assign Request-Id UUID
  └── modularity.core group
        ├── modularity.utm          ← capture UTM params
        ├── modularity.impersonate  ← swap auth user if impersonating
        ├── modularity.language     ← set locale / currency
        ├── modularity.loadLocalizedConfig  ← merge runtime config
        ├── modularity.navigation   ← share nav data with views
        └── inertia.middleware      ← share auth/flash/config with Inertia
  └── [modularity.panel group — authenticated panel routes only]
        ├── authorization           ← share profile shortcuts with layout
        ├── modularity.company.registration  ← company guard
        └── modularity.redirector   ← consume pending redirects
  └── Controller
```
