---
sidebarPos: 2
sidebarTitle: HasCacheDependents
---

# Core\HasCacheDependents

**Namespace**: `Unusualify\Modularous\Entities\Traits\Core\HasCacheDependents`

Defines which other model types should have their caches invalidated when this model changes. Merges three sources in priority order: the `RelationshipGraph` (automatic discovery), the model's `$cacheDependents` property, and the `modularous.cache.dependencies` config.

Used alongside `HasCaching` — `HasCaching` triggers invalidation, `HasCacheDependents` defines the scope.

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getCacheDependents` | `(): array` | Returns the merged list of dependent module names from all three sources |
| `getGraphDiscoveredDependents` | `(): array` | Returns only automatically discovered dependents from the relationship graph |
| `getManualDependents` | `(): array` | Returns only property-defined and config-defined dependents |
| `addCacheDependent` | `(string $module): static` | Adds a runtime cache dependent dynamically |
| `hasCacheDependents` | `(): bool` | Returns `true` if any dependents are registered |

---

## Resolution Order

1. **Relationship graph** — auto-discovered via `RelationshipGraph::getAffectedModules($modelClass)`
2. **Property** — `protected array $cacheDependents = [...]` on the model
3. **Config** — `config('modularous.cache.dependencies.{ModelClass}')` (full class name key)

---

## Configuration

```php
// Option 1: Property on the model
class Company extends Model
{
    use HasCaching, HasCacheDependents;

    protected array $cacheDependents = ['press_release', 'invoice'];
}

// Option 2: Config file (config/modularous.php)
'cache' => [
    'dependencies' => [
        \App\Models\Company::class => ['press_release'],
    ],
],

// Option 3: Dynamic (runtime)
$company->addCacheDependent('blog_post');
```

---

## Usage

```php
$company = Company::first();

// All dependents (merged)
$company->getCacheDependents();          // ['press_release', 'invoice', ...]

// Only automatic
$company->getGraphDiscoveredDependents();

// Only manual
$company->getManualDependents();

// Check
$company->hasCacheDependents();          // true
```
