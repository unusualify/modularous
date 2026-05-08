---
sidebarPos: 4
sidebarTitle: HasCreator
---

# HasCreator

**Namespace**: `Unusualify\Modularity\Entities\Traits\HasCreator`

Records which authenticated user created the model via a `CreatorRecord` morph. Supports custom creator overrides for admin-on-behalf-of workflows. Integrates with Spatie Permissions for company-level access control.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `saving` | Detects `custom_creator_id` override; clears fillable helpers |
| `saved` (new) | Creates `CreatorRecord` for current auth user (or custom creator if set) |
| `saved` (update) | Updates `CreatorRecord` if a custom creator was set |
| `deleting` / `forceDeleting` | Deletes the associated `CreatorRecord` |

---

## Relationships

```php
public function creatorRecord(): MorphOne     // → CreatorRecord
public function creator(): HasOneThrough      // → User through CreatorRecord
```

---

## Fillable Helpers

| Field | Description |
|-------|-------------|
| `custom_creator_id` | Override the auto-detected creator user ID |
| `custom_creator_type` | Override the creator model class |
| `custom_guard_name` | Override the guard name stored on the record |

---

## Computed Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `creator_record_exists` | `bool` | Pre-computed from `withExists('creatorRecord')` global scope |

---

## Scopes

| Scope | Description |
|-------|-------------|
| `scopeIsCreator($creator_id, $guardName)` | Records created by a specific user ID and guard |
| `scopeIsMyCreation($user, $guardName)` | Records created by the authenticated user |
| `scopeHasAccessToCreation($user, $guardName)` | Records the user can access: own creations + company-scoped |
| `scopeAuthorized($guardName)` | *(deprecated)* Use `scopeHasAccessToCreation` |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getCreatorModel` | `(): string` | Returns the creator model class (from `CreatorRecord` or default) |
| `getDefaultCreatorModel` | `(): string` (static) | Returns the default creator model (override via `$defaultHasCreatorModel`) |
| `getRolesHasAccessToCreation` | `(): array` | Returns roles that see all records (bypass ownership filter) |
| `getCompanyRolesHasAccessToCreation` | `(): array` | Returns company-level roles that see company-scoped records |

---

## Global Scopes

Registers `creator_record_exists` via `addGlobalScopesHasCreator()`.

---

## Configuration

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$defaultHasCreatorModel` | `string` | `User::class` | Creator model class |
| `$rolesHasAccessToCreation` | `array` | `['admin','manager','editor']` | Roles that bypass ownership filter |
| `$companyRolesHasAccessToCreation` | `array` | `['manager','client-manager']` | Roles that see company-wide records |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\HasCreator;

class Article extends Model
{
    use HasCreator;
}

// Read creator
$article->creator;              // User who created this
$article->creatorRecord;

// Filter queries
Article::isMyCreation()->get();
Article::isCreator($userId)->get();
Article::hasAccessToCreation()->get(); // current user's accessible records

// Override creator (e.g. admin creating on behalf of client)
$article->custom_creator_id = $clientUser->id;
$article->save();
```
