---
sidebarPos: 4
sidebarTitle: CompanyRegistrationMiddleware
---

# CompanyRegistrationMiddleware

**File**: `src/Http/Middleware/CompanyRegistrationMiddleware.php`  
**Alias**: `modularous.company.registration`  
**Part of**: `modularous.panel` group

Guards panel routes that require the authenticated user to have a valid company record. Currently a stub — the enforcement logic is commented out pending the company validation feature.

## Current Behaviour

The middleware passes all requests through without restriction:

```php
public function handle($request, Closure $next)
{
    return $next($request);
}
```

## Intended Behaviour (stub)

The commented-out block shows the planned enforcement:

```php
// if (! $request->routeIs('*profile*')) {
//     if (!auth()->user()->validCompany) {
//         return redirect()->route(Route::hasAdmin('profile'));
//     }
// }
```

When activated, this will:
- Skip the check for any route matching `*profile*` (to avoid redirect loops).
- Redirect users without a `validCompany` to the profile page to complete their registration.

## Notes

- The middleware is already registered in the `modularous.panel` group and will enforce the company check automatically once the logic is uncommented.
- `validCompany` is expected to be a computed attribute or relationship check on the User model.
