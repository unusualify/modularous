---
sidebarPos: 3
sidebarTitle: AuthorizationMiddleware
---

# AuthorizationMiddleware

**File**: `src/Http/Middleware/AuthorizationMiddleware.php`  
**Alias**: `authorization`  
**Part of**: `modularity.panel` group

Shares authorization-related view data with the Blade master layout. Runs only on authenticated panel routes.

## What It Does

Registers a view composer for `modularity::layouts.master`:

```php
view()->composer('modularity::layouts.master', function ($view) {
    $view->with([
        'authorization'          => get_modularity_authorization_config(),
        'profileShortcutSchema'  => $profileShortcutSchema,
        'profileShortcutModel'   => $profileShortcutModel,
        'loginShortcutSchema'    => $loginShortcutSchema,
        'loginShortcutModel'     => [],
    ]);
});
```

| Variable | Source | Description |
|----------|--------|-------------|
| `authorization` | `get_modularity_authorization_config()` | Role/permission config used to control UI visibility |
| `profileShortcutSchema` | `getFormDraft('profile_shortcut')` | Form schema for the profile quick-edit panel |
| `profileShortcutModel` | `UserRepository::getFormFields($user, $schema)` | Current user's profile field values |
| `loginShortcutSchema` | `getFormDraft('login_shortcut')` | Form schema for the login shortcut widget |
| `loginShortcutModel` | `[]` | Empty — populated client-side |

## Notes

- This middleware only affects Blade-rendered pages. For Inertia pages, authorization data is shared via `HandleInertiaRequests::share()` under the `authorization` prop.
- The `UserRepository` is resolved fresh on each request to ensure the profile model reflects the current authenticated state (including impersonation).
