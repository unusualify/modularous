---
sidebarPos: 30
sidebarTitle: UserOauth
---

# UserOauth

**File**: `src/Entities/UserOauth.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`

Stores an OAuth provider link for a user. Each record represents one linked provider (e.g. Google, GitHub) with the provider's unique ID and access token.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `token` | `string` | OAuth access token |
| `provider` | `string` | Provider name (e.g. `google`, `github`) |
| `avatar` | `string` | Avatar URL from the provider |
| `oauth_id` | `string` | Provider-specific user ID |
| `user_id` | `int` | Owning user |

## Relationships

### `user(): BelongsTo`

The Modularous user this OAuth link belongs to.

## Table

Resolved from `modularous.tables.user_oauths`, defaults to `um_user_oauths`.

## Related

- [User](./user) — parent user model
- [HasOauth](/system-reference/backend/entity-traits/auth/has-oauth) entity trait — adds OAuth methods to the User model
- [LoginController](/system-reference/backend/http/controllers/auth/login-controller) — OAuth authentication flow
