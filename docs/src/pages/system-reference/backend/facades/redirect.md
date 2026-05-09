---
sidebarPos: 15
sidebarTitle: Redirect
---

# Redirect

**Facade**: `Unusualify\Modularous\Facades\Redirect`  
**Accessor**: `modularous.redirect`  
**Underlying**: `Unusualify\Modularous\Services\RedirectService`

Provides smart redirect logic for panel routes — resolves where to send users after login, after an action, or when a previous route is unavailable. See [RedirectService](/system-reference/backend/services/redirect-service) for implementation details.

## Usage

```php
use Unusualify\Modularous\Facades\Redirect;

// Get the intended redirect target for the current user
$response = Redirect::intended();

// Redirect back to the previous panel route, falling back to a default
return Redirect::toPrevious('admin.dashboard');
```

## Notes

- Used by `RedirectorMiddleware` to handle post-login and post-action redirects.
- Integrates with `ManagePrevious` controller trait to store and restore previous route state across requests.
