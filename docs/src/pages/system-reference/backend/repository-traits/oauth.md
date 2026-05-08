---
sidebarPos: 4
sidebarTitle: OAuth Traits
---

# OAuth Repository Traits

This trait provides OAuth user management at the repository level. It pairs with the [`Auth\HasOauth`](../entity-traits/auth/overview) entity trait.

---

## OauthTrait

**Namespace**: `Unusualify\Modularity\Repositories\Traits\OauthTrait`

Handles user lookup, provider linking verification, provider token updates, and new user creation from OAuth provider data (e.g., Google, GitHub). Designed to work with Laravel Socialite's `User` contract.

### Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `oauthUser` | `(SocialiteUser $oauthUser): ?User` | Finds an existing user by email from the OAuth provider response |
| `oauthIsUserLinked` | `(SocialiteUser $oauthUser, string $provider): bool` | Checks whether the user already has a linked record for the given provider and OAuth ID |
| `oauthUpdateProvider` | `(SocialiteUser $oauthUser, string $provider): User` | Updates the token and avatar for an existing provider link, returns the user |
| `oauthCreateUser` | `(SocialiteUser $oauthUser): User` | Creates a new user (or finds by email) with the OAuth profile data and the configured default role |

### OAuth Login Flow

```
1. oauthUser($oauthUser)           → Find user by email
2. if user exists:
   ├─ oauthIsUserLinked(...)       → Check if provider is linked
   │   ├─ linked: oauthUpdateProvider(...)  → Update token/avatar
   │   └─ not linked: link the provider
   └─ return user
3. if user doesn't exist:
   └─ oauthCreateUser(...)         → Create user + link provider
```

### User Creation Details

`oauthCreateUser()` creates a user with:

| Field | Source |
|-------|--------|
| `email` | `$oauthUser->email` |
| `name` | `$oauthUser->user['given_name']` (falls back to `$oauthUser->name`) |
| `surname` | `$oauthUser->user['family_name']` (falls back to empty string) |
| `role` | `modularityConfig('oauth.default_role')` |
| `published` | `true` |

The method uses `firstOrNew` to avoid duplicate email records — if a user with the same email already exists, it returns the existing user instead of creating a new one.

### Configuration

The default role for newly created OAuth users is configured in:

```php
// config/modularity.php (or equivalent)
'oauth' => [
    'default_role' => 'member',
],
```

### Usage

```php
use Unusualify\Modularity\Repositories\Traits\OauthTrait;

class UserRepository extends Repository
{
    use OauthTrait;
}

// In an OAuth callback controller:
$oauthUser = Socialite::driver('google')->user();

$existingUser = $repo->oauthUser($oauthUser);

if ($existingUser) {
    if ($repo->oauthIsUserLinked($oauthUser, 'google')) {
        $repo->oauthUpdateProvider($oauthUser, 'google');
    }
} else {
    $user = $repo->oauthCreateUser($oauthUser);
}
```
