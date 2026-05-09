---
sidebarPos: 1
sidebarTitle: Overview
---

# Middleware

**Directory**: `src/Http/Middleware/`  
**Registration**: `src/Support/ModularousRoutes::generateRouteMiddlewares()`

Modularous registers its own middleware aliases and groups during the route bootstrapping phase. All aliases use the `modularous.*` prefix to avoid conflicts with application middleware.

## Middleware Aliases

| Alias | Class | Description |
|-------|-------|-------------|
| `modularous.auth` | [AuthenticateMiddleware](/system-reference/backend/http/middleware/authenticate) | Guards routes; stores intended URL; handles JSON 401 |
| `modularous.guest` | [RedirectIfAuthenticatedMiddleware](/system-reference/backend/http/middleware/redirect-if-authenticated) | Redirects already-authenticated users away from guest pages |
| `modularous.log` | [LogMiddleware](/system-reference/backend/http/middleware/log) | Injects `Request-Id` UUID into every request/response |
| `modularous.utm` | [UtmMiddleware](/system-reference/backend/http/middleware/utm) | Captures UTM params and shares them with views |
| `modularous.language` | [LanguageMiddleware](/system-reference/backend/http/middleware/language) | Resolves locale, timezone, and active currency per request |
| `modularous.impersonate` | [ImpersonateMiddleware](/system-reference/backend/http/middleware/impersonate) | Activates user impersonation from session |
| `modularous.loadLocalizedConfig` | [LoadLocalizedConfig](/system-reference/backend/http/middleware/load-localized-config) | Merges deferred and app-level config files at request time |
| `modularous.navigation` | [NavigationMiddleware](/system-reference/backend/http/middleware/navigation) | Shares navigation config with Blade layout views |
| `authorization` | [AuthorizationMiddleware](/system-reference/backend/http/middleware/authorization) | Shares profile/login shortcut schemas with the master layout |
| `modularous.company.registration` | [CompanyRegistrationMiddleware](/system-reference/backend/http/middleware/company-registration) | Guards routes that require a valid company (stub) |
| `modularous.redirector` | [RedirectorMiddleware](/system-reference/backend/http/middleware/redirector) | Consumes a pending redirect URL from `RedirectService` |
| `hostable` | [HostableMiddleware](/system-reference/backend/http/middleware/hostable) | Stub for host-based routing features |
| `inertia.middleware` | [HandleInertiaRequests](/system-reference/backend/http/middleware/handle-inertia-requests) | Inertia root view + shared props (auth, flash, config, store) |
| `role` | Spatie `RoleMiddleware` | Role-based route protection (Spatie Permission) |
| `permission` | Spatie `PermissionMiddleware` | Permission-based route protection (Spatie Permission) |
| `role_or_permission` | Spatie `RoleOrPermissionMiddleware` | Role OR permission route protection (Spatie Permission) |

## Middleware Groups

| Group | Middleware stack |
|-------|-----------------|
| `web.auth` | `web`, `modularous.auth:{guard}` |
| `api.auth` | `api`, `throttle:api`, `modularous.auth:{guard}` |
| `modularous.core` | `modularous.utm`, `modularous.impersonate`, `modularous.language`, `modularous.loadLocalizedConfig`, `modularous.navigation`, `inertia.middleware` |
| `modularous.panel` | `authorization`, `modularous.company.registration`, `modularous.redirector` |

## Route Stack by Type

Every route registered by Modularous belongs to one of four stacks:

| Route type | Middleware stack |
|------------|-----------------|
| `web` (public) | `web` + `modularous.log` + `modularous.core` |
| `webPanel` (authenticated admin) | `web.auth` + `modularous.log` + `modularous.core` + `modularous.panel` |
| `api` (public API) | `api` + `modularous.log` + `modularous.core` |
| `apiPanel` (authenticated API) | `api.auth` + `modularous.log` + `modularous.core` + `modularous.panel` |

## Request Flow

```
Incoming request
  └── modularous.log          ← assign Request-Id UUID
  └── modularous.core group
        ├── modularous.utm          ← capture UTM params
        ├── modularous.impersonate  ← swap auth user if impersonating
        ├── modularous.language     ← set locale / currency
        ├── modularous.loadLocalizedConfig  ← merge runtime config
        ├── modularous.navigation   ← share nav data with views
        └── inertia.middleware      ← share auth/flash/config with Inertia
  └── [modularous.panel group — authenticated panel routes only]
        ├── authorization           ← share profile shortcuts with layout
        ├── modularous.company.registration  ← company guard
        └── modularous.redirector   ← consume pending redirects
  └── Controller
```
