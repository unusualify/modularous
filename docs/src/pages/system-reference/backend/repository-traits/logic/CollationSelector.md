---
sidebarPos: 3
sidebarTitle: CollationSelector
---

# CollationSelector

**Namespace**: `Unusualify\Modularous\Repositories\Logic\CollationSelector`

Applies explicit MySQL collation to `LIKE` search queries on text columns, solving case/accent sensitivity mismatches that arise when the database collation differs from the connection default. Composes `CompilesJsonPaths`.

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$shouldUseSearchCollation` | `bool` | `false` | Per-repository override to force collation even when the global setting is off |
| `$collationSelectorColumns` | `array` | `char`, `varchar`, `tinytext`, `text`, `mediumtext`, `longtext`, `enum`, `set` | Column types that receive explicit collation |

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `setShouldUseSearchCollation` | `(bool $value): static` | Fluent setter for the per-repository collation override |
| `shouldUseSearchCollation` | `($query): bool` | Returns `true` when either `Modularous::shouldUseCollationForSearch()` or `$shouldUseSearchCollation` is set **and** the connection driver is MySQL |
| `isCollationQuery` | `($query): bool` | Returns `true` only for MySQL connections |
| `addSearchCollationToQuery` | `(Builder $query, string $field, mixed $value, ?Model $model): Builder` | Adds a collation-aware `LIKE` clause. Handles JSON paths (casts to `CHAR`), checks column types from `getColumnTypes()`, and falls back to a standard `orWhere LIKE` for non-text columns |
| `getCollationSelectorColumns` | `(): array` | Returns the list of column types that receive explicit collation |

## How Collation Is Applied

```
1. isCollationQuery()         → only applies on MySQL
2. shouldUseSearchCollation() → global flag or per-repo override
3. field contains '->'        → JSON path: CAST(field AS CHAR) COLLATE {collation} LIKE ?
4. field type in $collationSelectorColumns
                              → field COLLATE {collation} LIKE ?
5. otherwise                  → orWhere($field, LIKE, '%value%')
```

The collation string is read from the connection config (`collation` key) and defaults to `utf8mb4_unicode_ci`.

## Usage

```php
// Enable globally via config/modularous.php:
'search' => ['use_collation' => true]

// Enable for a specific repository only:
$repo->setShouldUseSearchCollation(true);

// CollationSelector is used internally inside searchIn() / searchInRelationships()
// — no direct call is needed in most cases.
```
