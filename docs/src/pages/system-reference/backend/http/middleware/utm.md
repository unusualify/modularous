---
sidebarPos: 15
sidebarTitle: UtmMiddleware
---

# UtmMiddleware

**File**: `src/Http/Middleware/UtmMiddleware.php`  
**Alias**: `modularous.utm`  
**Part of**: `modularous.core` group

Captures UTM tracking parameters from the incoming request and shares them with Blade layout views.

## What It Does

1. Calls `Utm::getParameters()` — this reads UTM params from the request and stores them in the session (see [UtmParameters](/system-reference/backend/services/utm-parameters)).
2. Registers a view composer for both layout views:
   ```php
   view()->composer([
       'modularous::layouts.app-inertia',
       'modularous::layouts.master',
   ], function ($view) {
       $view->with('utmParameters', Utm::getParameters());
   });
   ```

## Shared Variable

| Variable | Type | Description |
|----------|------|-------------|
| `utmParameters` | `array` | Captured UTM params from session: `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content` |

## Notes

- `Utm::getParameters()` is called twice — once to capture (and persist to session) and once inside the view composer to retrieve. The second call reads from the session, so it is safe even if the current request doesn't have UTM params.
- Parameters persist in the session for the configured TTL (see [UtmParameters](/system-reference/backend/services/utm-parameters)).
- For Inertia pages, `utmParameters` is available in the Blade wrapper; pass it to the Vue app via `storeData` if needed.
