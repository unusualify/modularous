---
sidebarPos: 13
sidebarTitle: RedirectIfAuthenticatedMiddleware
---

# RedirectIfAuthenticatedMiddleware

**File**: `src/Http/Middleware/RedirectIfAuthenticatedMiddleware.php`  
**Alias**: `modularity.guest`

Protects guest-only routes (login, register, password reset) by redirecting already-authenticated users away.

## What It Does

```php
public function handle($request, Closure $next, $guard = 'modularity')
{
    if ($this->authFactory->guard($guard)->check()) {
        return $this->redirector->to(modularityConfig('auth_login_redirect_path', '/'));
    }
    return $next($request);
}
```

If the user is already authenticated under the specified guard, they are redirected to `modularity.auth_login_redirect_path` (default: `'/'`).

## Configuration

```php
// config/modularity.php
'auth_login_redirect_path' => '/admin',
```

## Usage

Apply to guest-only routes:

```php
Route::middleware('modularity.guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});
```

## Notes

- The guard parameter defaults to `'modularity'` but can be overridden: `modularity.guest:web`.
- This is the inverse of `modularity.auth` — one protects routes that require authentication, the other protects routes that require the user to be a guest.
