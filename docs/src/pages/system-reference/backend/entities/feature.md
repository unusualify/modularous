---
sidebarPos: 9
sidebarTitle: Feature
---

# Feature

**File**: `src/Entities/Feature.php`  
**Namespace**: `Unusualify\Modularity\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`

Stores featured/starred content for bucket-based content curation. Each feature record links a target model to a named bucket with a sort position and an optional starred flag.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `featured_id` | `int` | Featured model ID |
| `featured_type` | `string` | Featured model class |
| `position` | `int` | Sort order within the bucket |
| `bucket_key` | `string` | Bucket identifier |
| `starred` | `bool` | Whether the item is starred |

## Relationships

### `featured(): MorphTo`

The model being featured.

## Scopes

### `scopeForBucket($query, $bucketKey): Collection`

Returns all featured models for a given bucket key, filtering out null (deleted) relations.

## Table

Resolved from `modularity.features_table`, defaults to `twill_features`.

## Related

- [HasBlocks](/system-reference/backend/entity-traits/secondary/has-blocks) — block editor for featured content
