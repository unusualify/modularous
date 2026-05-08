---
sidebarPos: 7
sidebarTitle: ModelHelpers
---

# Core\ModelHelpers

**Namespace**: `Unusualify\Modularity\Entities\Traits\Core\ModelHelpers`

The master composition trait. Composes `ManageEloquent`, `ManageModuleRoute`, `HasScopes`, `LogsActivity`, and `ChangeRelationships` into a single `use` statement. Most Modularous modules include this via `ModelHelpers`.

---

## Composed Traits

| Trait | Provides |
|-------|----------|
| `ManageEloquent` | `getTableColumns`, `definedRelations`, `definedRelationTypes` |
| `ManageModuleRoute` | `getRouteTitleColumnKey`, module route helpers |
| `HasScopes` | `scopePublished`, `scopeVisible`, `scopeDraft`, global scope registration |
| `LogsActivity` | Spatie activity log integration |
| `ChangeRelationships` | `wasChangedRelationships`, change tracking |

---

## Boot Behavior

`bootModelHelpers()`:
- Enables/disables activity logging based on auth state.
- On `saving`: captures dirty translated attribute values into `$oldTranslations`.
- On `saved`: logs translation changes to the Spatie activity log.

---

## Static Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$defaultAuthorizedModel` | `string` | `User::class` | Default model class for `HasAuthorizable` |
| `$authorizableRolesToCheck` | `array` | `['manager','account-executive']` | Roles bypassing authorization filter |
| `$assignableRolesToCheck` | `array` | `['editor','reporter']` | Roles bypassing assignment filter |
| `$defaultHasCreatorModel` | `string` | `User::class` | Default model class for `HasCreator` |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getTitleValue` | `(): mixed` | Returns the model's human-readable title (uses `getRouteTitleColumnKey`) |
| `getShowFormat` | `(): mixed` | Returns the value used for the admin "show" view |
| `setStateFormatted` | `(?State $state): string` | Returns a Vuetify chip HTML string for the given state |
| `setStateablePreview` | `(State $state): string` | Returns a chip preview for a state |
| `setStateablePreviewNull` | `(): string` | Returns a "No Status" chip |
| `getActivitylogOptions` | `(): LogOptions` | Returns Spatie activity log configuration (logs fillable + translated attributes, dirty only) |
| `lastActivities` | `(): MorphMany` | Returns up to 10 most recent activity log entries with causer eager-loaded |
| `numberOfX` | `(…): int` | Magic `__call` — resolves `numberOfComments()` → `comments()->count()` for any plural relationship |

---

## Magic Accessors

`__call($method, $arguments)` — Resolves `numberOf{Relation}()` patterns:
```php
$article->numberOfComments();   // → $article->comments()->count()
$article->numberOfImages();     // → $article->images()->count()
```

`__get($key)` — Returns values from `$spreadableMutatorAttributes` when set (integration with `HasSpreadable`).

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;

class Article extends Model
{
    use ModelHelpers;
}

$article->getTitleValue();
$article->numberOfComments();    // magic count
$article->lastActivities;        // MorphMany query
```
