---
sidebarPos: 16
sidebarTitle: sources
---

# sources

**File**: `src/Helpers/sources.php`

Application data helpers that assemble the configuration objects injected into Inertia pages on every request. These functions are called by `HandleInertiaRequests` middleware to build the shared `$page->props`.

## Locale / Timezone

### `getLocales`

```php
getLocales(): array
```

Returns the active locale list from `config('translatable.locales')`, normalising both simple (`['en', 'tr']`) and nested (`['tr' => ['TR', 'CY']]`) formats into a flat array of locale strings (e.g. `['en', 'tr-TR', 'tr-CY']`).

---

### `getTimeZoneList`

```php
getTimeZoneList(): Collection
```

Returns all PHP timezone identifiers with their UTC offset, sorted alphabetically. Cached forever under `timezones_list_collection`.

```php
// ['Africa/Abidjan' => 'Africa/Abidjan (UTC +00:00)', ...]
```

---

## Form Drafts

### `getFormDraft`

```php
getFormDraft(string $name, array $overwrites = [], array $excludes = [], bool $preserve = true): array
```

Loads a named form draft from `modularous.form_drafts.{$name}` config, merges `$overwrites`, and optionally removes `$excludes` keys. When `$preserve = true` uses `array_merge_recursive_preserve`; otherwise uses plain `array_merge`.

---

## Admin URL / Route Prefixes

### `adminRouteNamePrefix` / `adminUrlPrefix`

```php
adminRouteNamePrefix(): string
adminUrlPrefix(): string
```

Return the configured admin route name prefix and URL prefix respectively. Thin wrappers over `Modularous::getAdminRouteNamePrefix()` and `Modularous::getAdminUrlPrefix()`.

---

### `systemUrlPrefix` / `systemRouteNamePrefix`

```php
systemUrlPrefix(): string
systemRouteNamePrefix(): string
```

Return the system-settings URL prefix (default: `'system-settings'`) and its snake_case route name equivalent.

---

## Themes

### `builtInModularousThemes`

```php
builtInModularousThemes(): Collection
```

Returns a `Collection` of built-in Modularous SASS themes as `['theme-name' => 'Theme Name']`, scanned from `vue/src/sass/themes/*/` (excluding `customs/`).

---

### `customModularousThemes`

```php
customModularousThemes(): Collection
```

Returns custom themes from `resources/vendor/modularous/themes/*/` as `['theme-name' => 'Theme Name']`.

---

## Translations

### `get_translations`

```php
get_translations(): array
```

Returns all registered translations from the Laravel translator. Cached in the file store under `modularous-languages` for 600 seconds.

---

### `clear_translations`

```php
clear_translations(): void
```

Forgets the `modularous-languages` cache entry, forcing the next `get_translations()` call to rebuild.

---

## Inertia Shared Data Builders

The following functions each build one section of the data shared with every Inertia page. They are called from `HandleInertiaRequests::share()`.

### `get_modularous_navigation_config`

```php
get_modularous_navigation_config(): array
```

Returns the navigation config for the current user's role:

```php
[
    'current_url' => '...',
    'sidebar'     => [...],   // role-based: default / superadmin / client / guest
    'breadcrumbs' => [],
    'profileMenu' => [...],
    'sidebarBottom' => [...],
]
```

---

### `get_modularous_authorization_config`

```php
get_modularous_authorization_config(): array
```

Returns the user's authorization state:

```php
[
    'isSuperAdmin' => bool,
    'isClient'     => bool,
    'roles'        => ['admin', ...],
    'permissions'  => ['view-products' => true, ...],
]
```

All Gate abilities are evaluated and included as key → bool pairs.

---

### `get_modularous_impersonation_config`

```php
get_modularous_impersonation_config(): array
```

Returns the impersonation state and endpoints:

```php
[
    'active'         => bool,
    'impersonated'   => bool,
    'stopRoute'      => 'https://...',
    'route'          => 'https://.../:id',
    'fetchEndpoint'  => 'https://.../users',
    // input appearance defaults
]
```

---

### `get_modularous_localization_config`

```php
get_modularous_localization_config(): array
```

Returns locale settings and the merged language strings:

```php
[
    'locale'          => 'tr',
    'fallback_locale' => 'en',
    'lang'            => [...],  // fallback + current merged
]
```

---

### `get_modularous_head_layout_config`

```php
get_modularous_head_layout_config(array $data): array
```

Returns the page head data (title etc.) from `$data`, merging any `_headLayoutData` override.

---

### `get_modularous_inertia_main_configuration`

```php
get_modularous_inertia_main_configuration(array $data): array
```

Assembles the complete main layout configuration object shared with every Inertia page:

```php
[
    'headerTitle'       => '...',
    'hideDefaultSidebar' => false,
    'fixedAppBar'       => false,
    'appBarOrder'       => 0,
    'sidebarAttributes' => ['logoSymbol' => '...'],
    'navigation'        => get_modularous_navigation_config(),
    'impersonation'     => get_modularous_impersonation_config(),
    'authorization'     => get_modularous_authorization_config(),
]
```

---

### `get_modularous_ui_preferences`

```php
get_modularous_ui_preferences(): array
```

Merges PHP config defaults with the authenticated user's stored `ui_preferences`:

```php
[
    'sidebar'          => [...],
    'topbar'           => [...],
    'bottomNavigation' => [...],
]
```

Returns only config defaults for guests.

---

## Currency / VAT (Client-specific)

### `get_user_currency_vat_rates`

```php
get_user_currency_vat_rates(): Collection
```

Returns the VAT rates for the authenticated client user's payment country. Returns an empty collection for non-client users or guests.

---

### `get_user_payment_country_currencies`

```php
get_user_payment_country_currencies(): Collection
```

Returns the payment currencies available for the authenticated client user's country, derived from `get_user_currency_vat_rates()`.
