---
sidebarPos: 29
sidebarTitle: User
---

# User

**File**: `src/Entities/User.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Illuminate\Foundation\Auth\User` (Authenticatable)  
**Implements**: `HasLocalePreference`, `MustVerifyEmail`  
**Traits**: `HasApiTokens`, `HasFactory`, `HasRoles`, `IsTranslatable`, `ModelHelpers`, `Notifiable`, `HasFileponds`, `HasOauth`, `CanRegister`, `HasCompany`

The primary authenticatable user model. Supports roles and permissions (via Spatie), API tokens (Sanctum), OAuth provider linking, company association, avatar via Filepond, and locale preferences.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `name` | `string` | First name |
| `surname` | `string` | Last name |
| `email` | `string` | Email address |
| `password` | `string` | Hashed password |
| `company_id` | `int` | Associated company |
| `job_title` | `string` | Job title |
| `language` | `string` | Preferred locale |
| `timezone` | `string` | Preferred timezone |
| `phone` | `string` | Phone number |
| `country_id` | `int` | Country reference |
| `ui_preferences` | `array` | Sidebar/topbar preferences (JSON cast) |
| `published` | `bool` | Active/published status |
| `email_verified_at` | `datetime` | Email verification timestamp |

## Hidden Attributes

`password`, `remember_token`

## Appended Accessors

| Accessor | Type | Description |
|----------|------|-------------|
| `roles_meta` | `Collection` | Minimal role data (id, name, title) for the current user |
| `is_client` | `bool` | `true` if any role name starts with `client` |
| `is_superadmin` | `bool` | `true` if user has the `superadmin` role |

## Boot Behaviour

- **Creating**: sets a default password from `DEFAULT_USER_PASSWORD` env if none provided.
- **Updated**: clears `email_verified_at` when the email changes.
- **Global scope**: always eager-loads `rolesMetaRelation`.

## Relationships

### `rolesMetaRelation(): BelongsToMany`

Lightweight roles relation (only `id`, `name`, `title` columns) for the `roles_meta` accessor. Respects Spatie's team permission configuration.

## Methods

### `setImpersonating($id)` / `stopImpersonating()` / `isImpersonating()`

Session-based impersonation helpers.

### `sendGeneratePasswordNotification($token)` / `sendPasswordResetNotification($token)`

Dispatches the corresponding notification with the given token.

### `preferredLocale(): string`

Returns the user's `language` or the application's default locale.

### `avatar: string` *(accessor)*

Returns the avatar Filepond upload URL or a default anonymous image.

## Related

- [Company](./company) — the user's organisation
- [UserOauth](./user-oauth) — linked OAuth providers
- [Profile](./profile) — extended profile data
- [ProfileController](/system-reference/backend/http/controllers/profile-controller) — profile management UI
