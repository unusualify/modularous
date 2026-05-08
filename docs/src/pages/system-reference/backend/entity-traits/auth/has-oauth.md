---
sidebarPos: 2
sidebarTitle: HasOauth
---

# HasOauth

**Namespace**: `Unusualify\Modularity\Entities\Traits\Auth\HasOauth`

Links a `User` to one or more OAuth provider records (`UserOauth`) via a `HasMany` relationship. Provides a helper to create or associate a `UserOauth` record from a Laravel Socialite callback.

---

## Relationships

```php
public function providers(): HasMany   // → UserOauth records (foreign key: user_id)
```

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `linkProvider` | `(User $oauthUser, string $provider): UserOauth\|false` | Creates a `UserOauth` record from a Socialite user and saves it via `$this->providers()->save()` |

### `linkProvider` field mapping

| UserOauth field | Source |
|----------------|--------|
| `token` | `$oauthUser->token` |
| `avatar` | `$oauthUser->avatar` |
| `provider` | `$provider` (string, e.g. `'google'`) |
| `oauth_id` | `$oauthUser->id` |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\Auth\HasOauth;

class User extends Authenticatable
{
    use HasOauth;
}

// In an OAuth callback controller:
$socialiteUser = Socialite::driver('google')->user();
$user->linkProvider($socialiteUser, 'google');

// Query linked providers:
$user->providers()->where('provider', 'github')->first();
$user->providers()->get(); // all linked OAuth providers
```
