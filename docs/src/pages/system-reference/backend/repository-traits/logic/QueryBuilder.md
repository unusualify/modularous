---
sidebarPos: 9
sidebarTitle: QueryBuilder
---

# QueryBuilder

**Namespace**: `Unusualify\Modularity\Repositories\Logic\QueryBuilder`

The primary data-retrieval layer used by every `Repository`. Provides paginated listing, single-record lookup, multi-ID fetching, column-value filtering, and a flexible flat-list helper. Composes `MethodTransformers` (for caching and filter delegation) and `SerializeModel` (for cache serialisation).

## Core Retrieval Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `get` | `($with, $scopes, $orders, $perPage, $appends, $forcePagination, $id, $exceptIds)` | Runs the paginated query. Handles full-text search (translated + relationship fields), custom scopes, ordering, `id`-positioned pages, and post-load appends. Returns a `LengthAwarePaginator` or `Collection`. |
| `getPaginator` | `(same signature as get)` | Main entry point — transparently delegates to `getCached()` when caching is enabled for the `index` type; otherwise calls `get()` directly. |
| `paginate` | `(?Request $request): LengthAwarePaginator` | Convenience wrapper that reads `itemsPerPage`, `scopes`, `orders`, `eager`, `appends`, `exceptIds`, and `id` from the current HTTP request and calls `getPaginator()`. |
| `getCached` | `(same signature as get)` | Serialises the paginator result to an array (including items cast via `serializeModel()`), caches it, then reconstructs a `LengthAwarePaginator` on hit. Avoids serialising Laravel's paginator closure directly. |
| `getById` | `($id, $with, $withCount, $lazy, $scopes, $useDefaultScopes): Model` | Fetches a single record by primary key. Supports eager loading (`with`), count selects (`withCount`), lazy loading chains (`lazy`), optional scope filtering, and transparently includes soft-deleted records. Throws `ModelNotFoundException` if absent. |
| `getByIds` | `(array $ids, $appends, $with, $scopes, $orders, ...): Collection` | Fetches multiple records by IDs. Supports the same eager/lazy options as `getById` plus post-load append resolution. |
| `getByColumnValue` | `($column, $value, $with, $scopes, $orders, $isFormatted, $schema): Collection` | Fetches records by a single column value (scalar → `where`, array → `whereIn`). |
| `listAll` | `($with, $scopes, $orders): Collection` | Returns all records (unpaginated) with optional relations, scopes, and ordering. |
| `list` | `($column, $with, $scopes, $orders, $appends, $perPage, $exceptId, $forcePagination)` | Lightweight select-list helper. Resolves translatable columns, adds required foreign keys for eager-loaded `BelongsTo`/`MorphTo` relations, and avoids selecting absent columns. Supports optional pagination. |
| `formatWiths` | `($query, array $with): array` | Normalises the `$with` array — passes plain strings through unchanged, and converts associative `['functions' => [...]]` entries into closures applied in sequence. |

## Pagination Modes

`perPage` controls how `get()` returns results:

| Value | Behaviour |
|-------|-----------|
| `> 0` | Standard `paginate($perPage)` — returns a `LengthAwarePaginator` |
| `-1` | Fetches all rows, wraps them in a `LengthAwarePaginator` with `perPage = total` |
| `0` | Returns an empty `LengthAwarePaginator` (total is still counted from the query) |

## ID-Positioned Paging

When `$id` is provided to `get()` or `getPaginator()`, the paginator automatically jumps to the page containing that record:

```
1. Clone the filtered/ordered query
2. pluck('id') → build ordered ID list
3. array_search($id, $orderedIds) → position
4. page = floor(position / perPage) + 1
```

## Search Handling

The `scopes` array may include:

| Key | Type | Purpose |
|-----|------|---------|
| `search` | `string` | The search term |
| `searches` | `array` | Column names to search in |

Fields containing `.` in the `searches` array are treated as relationship fields and routed through `searchInRelationships()`. Fields matching `translatedAttributes` are handled by `TranslationsTrait::filterTranslationsTrait()`. Remaining fields are passed to `searchIn()` on the main table.

## Deprecated Methods

| Method | Replacement |
|--------|-------------|
| `getByIdWithScopes()` | `getById($id, ..., useDefaultScopes: true)` — removed in v1.0.0 |
| `$isFormatted` in `getByIds` | Removed in v1.0.0 — passed as `null` to suppress deprecation warning |

## Usage

```php
// Standard paginated listing from a controller
$items = $repo->getPaginator(
    with: ['images'],
    scopes: ['search' => 'Laravel', 'searches' => ['title', 'body']],
    orders: ['created_at' => 'desc'],
    perPage: 15,
);

// Request-driven pagination (API endpoints)
$items = $repo->paginate(request());

// Fetch single record with relations
$item = $repo->getById($id, with: ['tags', 'category'], withCount: ['comments']);

// Lightweight list for a select/autocomplete input
$options = $repo->list('title', with: ['category'], perPage: -1);
```
