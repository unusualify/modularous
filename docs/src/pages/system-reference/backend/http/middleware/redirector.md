---
sidebarPos: 12
sidebarTitle: RedirectorMiddleware
---

# RedirectorMiddleware

**File**: `src/Http/Middleware/RedirectorMiddleware.php`  
**Alias**: `modularous.redirector`  
**Part of**: `modularous.panel` group

Consumes a pending redirect URL stored by `RedirectService` and issues the redirect before the request reaches the controller.

## What It Does

```php
$redirectUrl = $this->redirectService->pull();
if ($redirectUrl) {
    return Redirect::to($redirectUrl);
}
return $next($request);
```

`RedirectService::pull()` reads the pending URL from the session or cache and **removes it** in the same operation. If a URL is present, the response is a redirect immediately — the controller is never invoked.

## When It Fires

Only on `modularous.panel` routes (authenticated admin panel). If no pending redirect is stored, the request continues normally.

## Typical Use Case

A controller stores a redirect URL for a future request (e.g., after a multi-step wizard or OAuth flow):

```php
// Step 1 controller — save destination
app(RedirectService::class)->store(route('admin.orders.index'));

// RedirectorMiddleware on the next panel request:
// → pulls the URL → redirects to admin.orders.index
// → subsequent requests pass through normally
```

## Related

- [RedirectService](/system-reference/backend/services/redirect-service) — the service that stores and retrieves the pending URL.
