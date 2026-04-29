---
sidebarPos: 2
sidebarTitle: array
---

# array

**File**: `src/Helpers/array.php`

Array manipulation helpers used throughout Modularous for merging, exporting, and transforming PHP arrays.

## Functions

### `array_merge_recursive_distinct`

```php
array_merge_recursive_distinct(array &$array1, array &$array2): array
```

Deep-merges two arrays. When the same key exists in both arrays and both values are arrays, it recurses. Otherwise the value from `$array2` **overwrites** `$array1`.

Unlike PHP's `array_merge_recursive`, this does not create sub-arrays for scalar key conflicts.

---

### `array_merge_recursive_preserve`

```php
array_merge_recursive_preserve(array ...$arrays): array
```

Variadic deep merge. Calls `array_merge_recursive_distinct` across all provided arrays in order. Used as the standard deep-merge utility across Modularous controllers and config helpers.

---

### `array_export`

```php
array_export(array $array, string $indent = ''): string
```

Converts an array to a formatted PHP `array(...)` string suitable for writing to a `.php` config file. Recursively indents nested arrays.

---

### `php_array_file_content`

```php
php_array_file_content(array $array): string
```

Wraps `array_export()` output in a full PHP file template:

```php
<?php

return array_export($array);
```

Used by code generators that write config files.

---

### `array_to_object`

```php
array_to_object(array $array): object
```

Recursively converts an associative array to a `stdClass` object tree using `json_decode(json_encode($array))`.

---

### `object_to_array`

```php
object_to_array(object $object): array
```

Recursively converts a `stdClass` object tree back to an associative array.

---

### `nested_array_merge`

```php
nested_array_merge(array $array1, array $array2): array
```

Alias for `array_merge_recursive_distinct` — accepts both arrays by value.

---

### `array_merge_conditional`

```php
array_merge_conditional(array $base, array $conditional, bool $condition): array
```

If `$condition` is `true`, merges `$conditional` into `$base` using `array_merge_recursive_distinct`. Otherwise returns `$base` unchanged.

---

### `change_array_file_array`

```php
change_array_file_array(string $filePath, string $key, mixed $value): void
```

Reads a PHP array config file, sets `$key` to `$value` using `Arr::set()`, then writes the updated array back to the file using `php_array_file_content()`.

---

### `add_route_to_config`

```php
add_route_to_config(string $filePath, string $routeName, array $routeConfig): void
```

Loads a route config file and appends `$routeConfig` under `routes.$routeName`. Uses `change_array_file_array` internally.

---

### `array_except`

```php
array_except(array $array, array|string $keys): array
```

Returns a copy of `$array` with the given `$keys` removed. Thin wrapper over `Arr::except()`.
