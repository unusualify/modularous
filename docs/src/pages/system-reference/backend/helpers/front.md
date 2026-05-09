---
sidebarPos: 9
sidebarTitle: front
---

# front

**File**: `src/Helpers/front.php`

Frontend-oriented helpers for resolving host URLs and SVG symbol identifiers used by the Modularous UI.

## Functions

### `getHost`

```php
getHost(): string
```

Returns the current application host URL (`scheme://host`) without a trailing slash. Used by `permalink` and `permalinkPrefix` input extensions to build full preview URLs.

---

### `getModularousDefaultUrls`

```php
getModularousDefaultUrls(): array
```

Returns an array of default URLs that the Modularous frontend injects into its global configuration:

```php
[
    'admin'  => adminUrlPrefix(),
    'system' => systemUrlPrefix(),
    // additional entries from modularous config
]
```

---

### `modularous_svg_symbol_exists`

```php
modularous_svg_symbol_exists(string $symbol): bool
```

Checks whether an SVG symbol with the given identifier exists in the compiled Modularous sprite sheet. Returns `true` if the symbol is found.

---

### `get_modularous_logo_symbol`

```php
get_modularous_logo_symbol(string|array $candidates): string
```

Accepts a single symbol name or an ordered list of candidates and returns the first one that exists in the SVG sprite. Falls back to `'main-logo'` if no candidate is found. Used for locale-specific logo variants (e.g. `mini-logo-dark-tr`, then `mini-logo-dark`).

---

### `get_modularous_locale_symbol`

```php
get_modularous_locale_symbol(string $locale): string
```

Returns the SVG symbol identifier for a given locale flag icon. Falls back to a generic globe symbol if no locale-specific flag is registered.
