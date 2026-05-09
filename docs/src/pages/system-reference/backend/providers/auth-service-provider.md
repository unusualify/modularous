---
sidebarPos: 2
sidebarTitle: AuthServiceProvider
---

# AuthServiceProvider

**Class**: `Unusualify\Modularous\Providers\AuthServiceProvider`  
**Source**: `src/Providers/AuthServiceProvider.php`  
**Extends**: `Illuminate\Foundation\Support\Providers\AuthServiceProvider`  
**Implements**: `Illuminate\Contracts\Support\DeferrableProvider`

Registers authorization gates, configures Laravel Horizon access, and customises the email verification flow. All gate definitions are loaded dynamically from the permissions table — no hardcoded ability strings in application code.

## `boot()`

Guards the entire boot body behind two runtime checks:

```php
if (exceptionalRunningInConsole() && database_exists() && Schema::hasTable(config('permission.table_names.permissions')))
```

This ensures gate definitions are only applied when the database is available and the permissions table has been migrated.

### Superadmin bypass

```php
Gate::before(function (User $user, $ability) {
    return $user->hasRole('superadmin') ? true : null;
});
```

Any user with the `superadmin` role passes every gate check without further evaluation.

### Static gates

| Gate | Logic |
|------|-------|
| `dashboard` | User must have the `dashboard` permission |
| `impersonate` | User's role must be `superadmin` |

### Dynamic gates

Every row in the `permissions` table becomes a named gate:

```php
foreach (Permission::all() as $permission) {
    Gate::define($permission->name, function ($user) use ($permission) {
        return $this->userHasPermission($user, [$permission->name]);
    });
}
```

### Horizon access

```php
Horizon::auth(function ($request) {
    return app()->environment('local')
        || $request->user()->is_superadmin
        || in_array($request->user()->email, [...]);
});
```

Horizon is accessible in local environments or for superadmins and any email addresses in the whitelist.

## `register()`

### Custom email verification URL

Generates a signed temporary URL pointing to `admin.verification.verify` instead of the default Laravel route:

```php
VerifyEmail::createUrlUsing(function ($notifiable) {
    return URL::temporarySignedRoute(
        'admin.verification.verify',
        Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
        ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
    );
});
```

### Custom verification mail

Overrides `VerifyEmail::toMailUsing()` to send a standard `MailMessage` with a localised subject and body.

## Helper methods

| Method | Description |
|--------|-------------|
| `authorize($user, $callback)` | Runs `$callback($user)` — thin wrapper keeping gate closures clean |
| `userHasRole($user, $roles)` | Checks if `$user->roles` is in the given array |
| `userHasPermission($user, $permissions)` | Checks if `$user->permissions` is in the given array |
