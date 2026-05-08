---
sidebarPos: 3
sidebarTitle: ApiController
---

# ApiController

**File**: `src/Http/Controllers/ApiController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers`  
**Extends**: `CoreController`  
**Traits**: `ApiResponses`, `ApiVersioning`, `ApiAuthentication`, `ApiRateLimiting`, `ApiValidation`, `ApiPagination`, `ApiFiltering`, `ApiSorting`, `ApiRelationships`

Abstract base controller for RESTful JSON APIs. Provides versioning, rate limiting, relationship loading, bulk operations, and a standardised response envelope on top of `CoreController`.

## Constructor

```php
public function __construct(Application $app, Request $request)
```

Calls `CoreController::__construct()` then runs:

1. `setApiVersion()` — resolves version from request header or default.
2. `setApiResourceClasses()` — finds the API resource and collection classes.
3. `setApiDefaults()` — loads per-route API config (per-page limits, includes, etc.).

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$apiVersion` | `string` | `'v1'` | Active API version |
| `$defaultPerPage` | `int` | `15` | Default items per page |
| `$maxPerPage` | `int` | `100` | Maximum allowed items per page |
| `$defaultIncludes` | `array` | `[]` | Relationships always loaded |
| `$availableIncludes` | `array` | `[]` | Relationships the client may request |
| `$apiResourceClass` | `string` | — | `JsonResource` class for single items |
| `$apiResourceCollectionClass` | `string` | — | `ResourceCollection` class |
| `$wrapResponses` | `bool` | `true` | Wrap data in a `data` envelope |
| `$responseMetadata` | `array` | `[]` | Extra fields merged into every response |

## Standard CRUD Endpoints

| Method | HTTP | Path | Description |
|--------|------|------|-------------|
| `index()` | GET | `/api/{resource}` | Paginated list with filtering and sorting |
| `show(int $id)` | GET | `/api/{resource}/{id}` | Single item with includes |
| `store()` | POST | `/api/{resource}` | Create — returns 201 |
| `update(int $id)` | PUT | `/api/{resource}/{id}` | Update — returns 200 |
| `destroy(int $id)` | DELETE | `/api/{resource}/{id}` | Soft-delete — returns 200 |

## Extended Endpoints

### `bulk(): JsonResponse`

Fetches multiple items by an array of IDs. Accepts `ids[]` in the request body.

### `search(): JsonResponse`

Full-text search across the module's searchable columns. Returns a paginated collection.

### `filters(): JsonResponse`

Returns all available filters, sort fields, and includable relationships for the client to use in subsequent requests.

### `meta(): JsonResponse`

Returns metadata about the resource — relationship counts, available scopes, and config flags.

## Pagination

### `getPerPage(): int`

Returns `per_page` from the request, clamped between `1` and `$maxPerPage`.

## Relationships / Includes

### `getIncludes(): array`

Parses the `include` query parameter (comma-separated) and validates each name against `$availableIncludes`.

### `getIncludesForEagerLoading(): array`

Returns includes formatted for `with()` — may attach constraints (e.g. ordering) to specific relationships.

## Response Helpers

### `respondWithResource($resource, int $status = 200): JsonResponse`

Wraps a single `JsonResource` instance in the configured envelope and returns it.

### `respondWithCollection($collection, int $status = 200): JsonResponse`

Wraps a `ResourceCollection` with pagination meta.

### `respondWithData($data, int $status = 200): JsonResponse`

Returns arbitrary data. When `$wrapResponses` is `true`, nests under a `data` key.

## Rate Limiting

Rate limit headers (`X-RateLimit-Limit`, `X-RateLimit-Remaining`) are added to every response via the `ApiRateLimiting` trait. Limits are configured per route in the module config.

## Versioning

The API version is read from the `Accept` header (e.g. `Accept: application/vnd.api+json;version=v2`) or falls back to `$apiVersion`. The resolved version is available throughout the request lifecycle.
