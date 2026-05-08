---
sidebarPos: 3
sidebarTitle: column
---

# column

**File**: `src/Helpers/column.php`

Helpers for configuring and translating data-table column definitions passed to Modularous index views.

## Functions

### `configure_table_columns`

```php
configure_table_columns(array $columns): array
```

Runs each column definition through `HeaderHydrator`, which resolves defaults (width, sortable, type, etc.) and returns the normalized column array ready for the Vue data table.

---

### `hydrate_table_column_translation`

```php
hydrate_table_column_translation(array $column): array
```

Translates the column's `title` key using the `table-headers.*` translation namespace:

```php
$translation = ___("table-headers.{$column['title']}");
```

If a translation exists for the key, it replaces the raw `title` value. Falls back to the original value if no translation is found.

---

### `hydrate_table_columns_translations`

```php
hydrate_table_columns_translations(array $columns): array
```

Maps `hydrate_table_column_translation` over an array of column definitions. Called internally by `configure_table_columns` after hydration.
