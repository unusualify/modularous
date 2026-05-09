---
sidebarPos: 3
sidebarTitle: CurrentUser
---

# CurrentUser

**Class**: `Unusualify\Modularous\Http\ViewComposers\CurrentUser`  
**Source**: `src/Http/ViewComposers/CurrentUser.php`

Injects the authenticated user into every view as `$currentUser`. Uses Modularous configured auth guard rather than the default Laravel guard, ensuring the correct user is resolved in multi-guard setups.

## Injected Variable

| Variable | Type | Description |
|----------|------|-------------|
| `currentUser` | `array\|null` | The authenticated user's profile data, or `null` when no user is authenticated |

## Behaviour

1. Resolves the auth guard name from `Modularous::getAuthGuardName()`.
2. Retrieves the authenticated user via `AuthFactory::guard($guardName)->user()`.
3. If a user is found, passes it through the `get_user_profile($user)` helper, which normalises the model into a plain array with the fields expected by the frontend (name, email, avatar, role, etc.).
4. Exposes the result as `compact('currentUser')` — `null` when unauthenticated.

## Usage in Views

```blade
{{-- Blade --}}
@if($currentUser)
    Welcome, {{ $currentUser['name'] }}
@endif
```

```js
// Inertia (shared props)
const { currentUser } = usePage().props
```

## Configuration

The guard name is read from the Modularous config:

```php
// config/modularous.php
'auth' => [
    'guard' => 'web',   // used by Modularous::getAuthGuardName()
],
```
