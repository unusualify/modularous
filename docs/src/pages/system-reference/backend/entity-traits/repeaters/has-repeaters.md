---
sidebarPos: 1
sidebarTitle: HasRepeaters
---

# HasRepeaters

**Namespace**: `Unusualify\Modularous\Entities\Traits\HasRepeaters`

Enables nested "repeater" blocks on a model. Each repeater block is its own `Repeater` model instance that can carry images (`HasImages`), files (`HasFiles`), Filepond uploads (`HasFileponds`), and prices (`HasPriceable`). These four traits are automatically composed onto the `Repeater` model — no configuration required.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `retrieved` | Loads `repeaterRoles` (all distinct roles) and `repeaterLocaleRoles` (roles grouped by locale) from the database |

---

## Relationship

```php
public function repeaters(?string $role = null, ?string $locale = null): MorphMany
```

Returns `Repeater` records filtered by optional `role` and `locale`.

---

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$repeaterRoles` | `array` | Distinct role names from all repeater records (populated on `retrieved`) |
| `$repeaterLocaleRoles` | `array` | Role names grouped by locale (populated on `retrieved`) |

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getRepeaterField` | `(string $field, ?string $locale = null, mixed $default = null): mixed` | Returns a single field value from a repeater; `$field` uses dot-notation: `'role.nested.key'` |
| `getRepeaterRoles` | `(): array` | Returns all distinct roles for this model's repeaters |
| `getRepeaterLocaleRoles` | `(): array` | Returns roles grouped by locale |
| `hasRepeaterRole` | `(string $role): bool` | Returns `true` if any repeater with the given role exists |
| `hasRepeaterLocaleRole` | `(string $role, ?string $locale = null): bool` | Returns `true` if a locale-specific repeater role exists |
| `isRepeaterValueEqual` | `(string $key, string $value, ?string $locale = null): bool` | Checks if any repeater's field matches the given value (key uses dot-notation) |

---

## Usage

### Basic setup

```php
use Unusualify\Modularous\Entities\Traits\HasRepeaters;

class Article extends Model
{
    use HasRepeaters;
}
```

### Accessing repeaters

```php
// All repeaters for a role
$article->repeaters('gallery')->get();

// Locale-specific
$article->repeaters('pricing', 'fr')->get();

// Check existence
$article->hasRepeaterRole('gallery');              // bool
$article->hasRepeaterLocaleRole('gallery', 'en');  // bool
```

### Reading repeater data

```php
foreach ($article->repeaters('gallery')->get() as $slide) {
    $slide->image('photo');         // from HasImages
    $slide->file('download');       // from HasFiles
    $slide->basePrice;              // from HasPriceable
    $slide->getRepeaterField('caption');
}

// Dot-notation for nested content
$article->getRepeaterField('info.subtitle');
$article->getRepeaterField('info.subtitle', 'fr');
```

### Checking a value

```php
$article->isRepeaterValueEqual('status.code', 'active');
```

---

## Notes

- Repeater records are stored in a shared `repeaters` table with `role`, `locale`, `position`, and JSON `content` columns.
- Repeater records are ordered by their `position` column.
- Each `Repeater` model automatically has `HasFiles`, `HasImages`, `HasPriceable`, and `HasFileponds` mixed in.
