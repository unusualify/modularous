---
sidebarPos: 1
sidebarTitle: CacheTags
---

# CacheTags

**File**: `src/Services/Concerns/CacheTags.php`

`CacheTags` generates the tag name arrays used by every tagged cache operation. It defines a three-level tag hierarchy — global prefix, module, route — plus a relation-scoped tag for granular per-record invalidation.

## Tag Hierarchy

```
{prefix}                                  ← global tag (all modularous caches)
{prefix}:{Module}                         ← module tag (all routes in this module)
{prefix}:{Module}:{Route}                 ← route tag (one specific route/submodule)
{prefix}:rel:{ModelName}:{id}             ← relation tag (caches referencing this record)
```

## Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `getModuleTags(string $moduleName, bool $onlyModule = false): array` | `['{prefix}:{Module}']` or `['{prefix}', '{prefix}:{Module}']` | Tags for a whole module. `onlyModule: true` omits the global prefix tag. |
| `getModuleRouteTags(string $moduleName, string $moduleRouteName, bool $onlyRoute = false): array` | Up to 3 tags | Tags for a specific route. `onlyRoute: true` returns only the route tag. |
| `getTypeTags(string $moduleName, string $moduleRouteName, string $type): array` | 4 tags | Full hierarchy down to type level (used for internal cache keys). |
| `generateRelationTag(string $modelClass, $id): string` | Single tag string | `{prefix}:rel:{ModelName}:{id}` — scoped to one record. |
| `generateRelationTags(array $relations): array` | Array of tag strings | Bulk version; accepts `['ModelClass' => id]` or `['ModelClass' => [id1, id2]]`. |

## Examples

```php
// All tags when writing a route-scoped cache entry
$tags = $this->getModuleRouteTags('Orders', 'order');
// ['modularous', 'modularous:Orders', 'modularous:Orders:Order']

// Only route tag when doing a narrow invalidation
$tags = $this->getModuleRouteTags('Orders', 'order', onlyRoute: true);
// ['modularous:Orders:Order']

// Relation tags for a belongs-to-many write
$tags = $this->generateRelationTags(['Company' => 5, 'Tag' => [1, 3]]);
// ['modularous:rel:Company:5', 'modularous:rel:Tag:1', 'modularous:rel:Tag:3']
```

## Notes

- Module and route names are converted to StudlyCase via `Str::studly()` before generating tags, so `orders` and `Orders` produce the same tag.
- The `getPrefix()` abstract method must be implemented by the consuming class (returns `'modularous'` in `ModularousCacheService`).
