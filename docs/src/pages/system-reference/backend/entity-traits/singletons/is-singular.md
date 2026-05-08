---
sidebarPos: 2
sidebarTitle: IsSingular
---

# IsSingular

**Namespace**: `Unusualify\Modularity\Entities\Traits\IsSingular`

Stores all fillable fields for a model as a JSON blob in the shared `modularity_singletons` table. There is exactly one record per model type — no dedicated table or migration is required.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `creating` | Sets `singleton_type = static::class`; serializes fillable attributes into `content` JSON; removes individual attributes from the model |
| `updating` | Re-serializes fillable attributes into `content` JSON; removes individual attributes |
| `retrieved` | Deserializes `content` JSON back onto the model as individual attributes; removes `content` and `singleton_type` from the visible attributes |

Also registers `SingularScope` as a global scope to ensure queries only return the current model type's record.

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `single` | `(): static` (static) | Returns the singleton record, creating it if it doesn't exist |
| `getTable` | `(): string` (final) | Always returns the `modularity_singletons` table name (configurable via `modularity.tables.singletons`) |
| `isPublished` | `(): bool` | Returns the `published` field value (from JSON content) |

---

## Scopes

| Scope | Description |
|-------|-------------|
| `scopePublished($query)` | Records where `content->published` is `true` |

---

## Configuration

The table name can be overridden in `config/modularity.php`:
```php
'tables' => [
    'singletons' => 'modularity_singletons',
]
```

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\IsSingular;

class SiteSettings extends Model
{
    use IsSingular;

    protected $fillable = ['site_name', 'logo_url', 'contact_email', 'published'];
}

// Retrieve (or create) the singleton
$settings = SiteSettings::single();

// Read attributes (deserialized from JSON)
$settings->site_name;
$settings->contact_email;

// Update
$settings->site_name = 'My Platform';
$settings->save();

// Check published state
$settings->isPublished();
SiteSettings::published()->first();
```

::: info No migration needed
`IsSingular` models share the `modularity_singletons` table. A `type` column discriminates between different singletons. No additional migration is required beyond the package install.
:::
