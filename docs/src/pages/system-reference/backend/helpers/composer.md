---
sidebarPos: 5
sidebarTitle: composer
---

# composer

**File**: `src/Helpers/composer.php`

Helpers for Composer package introspection, environment detection, and `.env` file manipulation.

## Functions

### `get_installed_composer`

```php
get_installed_composer(): array
```

Reads and decodes `vendor/composer/installed.json`, returning the full installed package manifest as a PHP array.

---

### `get_package_installed_version`

```php
get_package_installed_version(string $package): string|null
```

Searches the installed Composer packages for `$package` (e.g. `unusualify/modularity`) and returns its installed version string, or `null` if not found.

---

### `is_modularity_development`

```php
is_modularity_development(): bool
```

Returns `true` when the `unusualify/modularity` package source type is `path` (i.e. it is loaded from a local path repository, as in a development monorepo setup).

---

### `is_modularity_production`

```php
is_modularity_production(): bool
```

Returns `true` when `is_modularity_development()` is `false` — i.e. the package is installed from Packagist or a VCS source.

---

### `get_modularity_vendor_dir`

```php
get_modularity_vendor_dir(): string
```

Returns the absolute path to the Composer vendor directory (e.g. `/var/www/vendor`).

---

### `get_modularity_vendor_path`

```php
get_modularity_vendor_path(string $path = ''): string
```

Appends `$path` to the vendor directory: `vendor/unusualify/modularity/{$path}`.

---

### `get_modularity_src_path`

```php
get_modularity_src_path(string $path = ''): string
```

Returns the path to the `src/` directory inside the package: `vendor/unusualify/modularity/src/{$path}`.

---

### `modularity_path`

```php
modularity_path(string $path = ''): string
```

Alias for `get_modularity_vendor_path()`. Preferred shorthand in most internal callers.

---

### `get_package_version`

```php
get_package_version(string $package): string
```

Returns the installed version of any Composer package. Wrapper around `get_package_installed_version` with a fallback to `'unknown'`.

---

### `set_env_file`

```php
set_env_file(string $key, string $value, string $envPath = null): void
```

Updates or inserts a `KEY=value` pair in the `.env` file. Uses a regex replace to overwrite an existing key or appends the pair if the key is not present. `$envPath` defaults to `base_path('.env')`.
