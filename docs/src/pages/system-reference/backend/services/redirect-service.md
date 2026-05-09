---
sidebarPos: 19
sidebarTitle: RedirectService
---

# RedirectService

**File**: `src/Services/RedirectService.php`  
**Facade**: `Unusualify\Modularous\Facades\Redirect`

A minimal service for storing and retrieving a **post-authentication redirect URL** across requests. Supports both session and cache storage so the intended destination survives a redirect to the login page.

## How It Works

When a guest visits a protected route, the URL is saved via `set()`. After successful login, `pull()` retrieves the URL and immediately clears it, then the controller redirects the user to their original destination.

## Key Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `set` | `set(string $url, ?int $ttlSeconds, bool $useCache): void` | Store the redirect URL. Uses session by default; pass `$useCache = true` to store in cache instead (default TTL 600 s). |
| `get` | `get(): ?string` | Retrieve the stored URL. Checks session first, then cache. Returns `null` if nothing is stored. |
| `pull` | `pull(): ?string` | Retrieve the URL and immediately clear it from both session and cache. The primary method for post-login redirects. |
| `clear` | `clear(): void` | Remove the URL from both session and cache without returning it. |

## Session & Cache Keys

Both storage modes use the same key: `modularous.redirect_url`.

## Typical Usage

```php
use Unusualify\Modularous\Facades\Redirect;

// In a middleware — save the intended URL before redirecting to login
public function handle($request, Closure $next)
{
    if (!auth()->check()) {
        Redirect::set($request->url());
        return redirect()->route('login');
    }
    return $next($request);
}

// In the login controller — redirect to intended URL after authentication
public function authenticated(Request $request, $user)
{
    return redirect(Redirect::pull() ?? route('dashboard'));
}
```

## Session vs Cache Storage

| Mode | Use when |
|------|----------|
| Session (default) | Standard web authentication — session persists across the login redirect |
| Cache | API-based or stateless flows where the session may not carry over |

```php
// Cache mode with custom TTL
Redirect::set($url, ttlSeconds: 300, useCache: true);
```
