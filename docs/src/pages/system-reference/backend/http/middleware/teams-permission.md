---
sidebarPos: 14
sidebarTitle: TeamsPermissionMiddleware
---

# TeamsPermissionMiddleware

**File**: `src/Http/Middleware/TeamsPermissionMiddleware.php`

Activates Spatie Permission's **team context** for the authenticated user by calling `setPermissionsTeamId()` at the start of each request.

## What It Does

```php
if (!empty(auth()->user())) {
    setPermissionsTeamId(session('team_id'));
}
```

Reads the active team ID from `session('team_id')` (set during login) and passes it to Spatie Permission's global team context function. From this point on, all `can()` checks and `role`/`permission` middleware evaluations are scoped to that team.

## When to Use

Apply this middleware when your application uses Spatie Permission with `teams` enabled:

```php
// config/permission.php
'teams' => true,
```

Add it to the panel middleware stack:

```php
// In a service provider or RouteServiceProvider
Route::middlewareGroup('modularity.panel', [
    'authorization',
    'modularity.company.registration',
    'modularity.redirector',
    TeamsPermissionMiddleware::class,
]);
```

## Notes

- `setPermissionsTeamId()` is provided by `spatie/laravel-permission`. If you are not using teams, this middleware has no effect.
- The `team_id` is stored in the session at login — typically from the user's primary team or a team-selection step.
- The commented-out `auth('api')` block in the source is a placeholder for API token-based team ID extraction.
