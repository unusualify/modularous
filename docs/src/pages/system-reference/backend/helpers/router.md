---
sidebarPos: 15
sidebarTitle: router
---

# router

**File**: `src/Helpers/router.php`

Routing and URL helpers for resolving route names, building query strings, and merging URL parameters.

## Functions

### `previous_route_name`

```php
previous_route_name(): string|null
```

Returns the route name of the previous URL (from `url()->previous()`), or `null` if the URL does not match any registered route. Uses the router's internal route collection to do a reverse lookup.

```php
$prev = previous_route_name();
// → 'admin.products.index'
```

---

### `array_to_query_string`

```php
array_to_query_string(array $data): string
```

Converts a data array to a URL query string. Objects and associative arrays are JSON-encoded before being passed to `http_build_query()` with RFC 3986 encoding:

```php
array_to_query_string(['filter' => ['status' => 'active'], 'page' => 2]);
// → 'filter=%7B%22status%22%3A%22active%22%7D&page=2'
```

---

### `merge_url_query`

```php
merge_url_query(string $url, object|array $data): string
```

Parses an existing URL, merges `$data` into its existing query parameters, and returns the reconstructed URL. Accepts both arrays and objects for `$data`.

```php
merge_url_query('https://app.test/admin/products?page=1', ['page' => 2, 'sort' => 'name']);
// → 'https://app.test/admin/products?page=2&sort=name'
```

---

### `resolve_route`

```php
resolve_route(string|array $definition): string
```

Resolves a route definition to a URL. Accepts:
- A plain string route name: `'admin.products.index'`
- An array `[$routeName, $params]`

Tries `Route::hasAdmin()` first (for admin-prefixed routes), falls back to `Route::has()`. If route parameters are present, they are extracted and the remainder is appended as a query string.

```php
resolve_route('products.index');
// → 'https://app.test/admin/products'

resolve_route(['products.show', ['product' => 1, 'tab' => 'details']]);
// → 'https://app.test/admin/products/1?tab=details'
```
