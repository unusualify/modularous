---
sidebarPos: 3
sidebarTitle: HasAuthorizable
---

# HasAuthorizable

**Namespace**: `Unusualify\Modularity\Entities\Traits\HasAuthorizable`

Attaches a per-record `Authorization` morph record, enabling fine-grained ownership and access control on individual model instances. Integrates with Spatie Permissions for role-based access checks.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `saving` | If `authorized_id` is set and valid, marks model as authorizing; clears fillable helpers |
| `saved` | Creates/updates the `Authorization` record; touches `updated_at` if model wasn't otherwise dirty |
| `deleting` / `forceDeleting` | Deletes the associated `Authorization` record |

---

## Relationships

```php
public function authorizationRecord(): MorphOne     // → Authorization
public function authorizedUser(): HasOneThrough     // → User through Authorization
```

---

## Computed Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `is_authorized` | `bool` | `true` if an authorization record exists |
| `authorization_record_exists` | `bool` | Pre-computed via `withExists('authorizationRecord')` global scope |

---

## Fillable Helpers

| Field | Description |
|-------|-------------|
| `authorized_id` | Set to a user ID to create/update the authorization on save |
| `authorized_type` | Set to a model class string to override the authorized model type |

---

## Scopes

| Scope | Description |
|-------|-------------|
| `scopeHasAuthorization($user)` | Records authorized for the given user (or current auth user) |
| `scopeIsAuthorizedToYou($user)` | Records authorized specifically to the given user's ID |
| `scopeIsAuthorizedToYourRole($user)` | Records authorized to any user sharing the current user's roles |
| `scopeHasAnyAuthorization()` | Records with any non-null authorization |
| `scopeUnauthorized()` | Records with no authorization record |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getAuthorizedModel` | `(): string` | Returns the authorized model class (from record or default) |
| `getDefaultAuthorizedModel` | `(): string` (static) | Returns the default model class (default: `User::class`, override via `$defaultAuthorizedModel`) |
| `hasAuthorizationUsage` | `(?mixed $user = null): bool` | Returns `true` if the current user has permission to manage authorizations on this record |

---

## Global Scopes

Registers `authorization_record_exists` via `addGlobalScopesHasAuthorizable()`:
- Adds `withExists('authorizationRecord')` so `$model->is_authorized` avoids a lazy-load.

---

## Configuration

| Property | Type | Description |
|----------|------|-------------|
| `$defaultAuthorizedModel` | `string` | Model class to use when no authorization record exists (default: `User::class`) |
| `$authorizableRolesToCheck` | `array` | Spatie roles that bypass the authorization filter entirely |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\HasAuthorizable;

class Report extends Model
{
    use HasAuthorizable;
}

// Assign authorization
$report->authorized_id = $user->id;
$report->save();

// Read
$report->authorizationRecord;
$report->authorizedUser;
$report->is_authorized;           // true

// Query
Report::isAuthorizedToYou()->get();
Report::hasAuthorization()->get();
Report::unauthorized()->get();
```
