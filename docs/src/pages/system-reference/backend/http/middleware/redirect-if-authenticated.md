---
sidebarPos: 13
sidebarTitle: RedirectIfAuthenticatedMiddleware
---

# RedirectIfAuthenticatedMiddleware

**File**: `src/Http/Middleware/RedirectIfAuthenticatedMiddleware.php`  
**Alias**: `modularous.guest`

Protects guest-only routes (login, register, password reset) by redirecting already-authenticated users away.

## What It Does

```php
public function handle($request, Closure $next, $guard = 'modularous')
{
    if ($this->authFactory->guard($guard)->check()) {
        return $this->redirector->to(modularousConfig('auth_login_redirect_path', '/'));
    }
    return $next($request);
}
```

If the user is already authenticated under the specified guard, they are redirected to `modularous.auth_login_redirect_path` (default: `'/'`).

## Configuration

```php
// config/modularous.php
'auth_login_redirect_path' => '/admin',
```

## Usage

Apply to guest-only routes:

```php
Route::middleware('modularous.guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});
```

## Notes

- The guard parameter defaults to `'modularous'` but can be overridden: `modularous.guest:web`.
- This is the inverse of `modularous.auth` — one protects routes that require authentication, the other protects routes that require the user to be a guest.
