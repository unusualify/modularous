---
sidebarPos: 13
sidebarTitle: migrations
---

# migrations

**File**: `src/Helpers/migrations_helpers.php`

Blueprint helper functions that provide standardized field presets for Modularous migration files. They respect the `use_big_integers_on_migrations` config option to switch between `int` and `bigInt` column types.

## Integer Type Helpers

### `modularousIncrementsMethod`

```php
modularousIncrementsMethod(): string
```

Returns `'bigIncrements'` or `'increments'` depending on the `use_big_integers_on_migrations` config flag.

### `modularousIntegerMethod`

```php
modularousIntegerMethod(): string
```

Returns `'bigInteger'` or `'integer'` depending on the same config flag.

---

## Table Field Presets

### `createDefaultTableFields`

```php
createDefaultTableFields(Blueprint $table, bool $has_name = true): void
```

Adds the primary key column using `modularousIncrementsMethod()`. Minimal base — does not add `name`, timestamps, or soft deletes.

---

### `createDefaultExtraTableFields`

```php
createDefaultExtraTableFields(
    Blueprint $table,
    bool $softDeletes = true,
    bool $published = true,
    bool $publishDates = false,
    bool $visibility = false
): void
```

Adds optional standard columns:

| Parameter | Column added |
|-----------|-------------|
| `$published = true` | `published` (boolean, default false) |
| `$publishDates = true` | `publish_start_date`, `publish_end_date` (nullable timestamps) |
| `$visibility = true` | `public` (boolean, default true) |
| always | `created_at`, `updated_at` |
| `$softDeletes = true` | `deleted_at` |

---

### `createDefaultTranslationsTableFields`

```php
createDefaultTranslationsTableFields(
    Blueprint $table,
    string $modelName,
    string $tableName = null,
    string $foreignKey = null
): void
```

Scaffolds a standard `*_translations` pivot table:
- `id` (increments)
- `{model}_id` (unsigned integer, foreign key → parent table with CASCADE delete)
- `deleted_at`, `created_at`, `updated_at`
- `locale` (string 7, indexed)
- `active` (boolean, default true)
- Unique index on `({model}_id, locale)`

Handles long model names (> 18 chars) by using `abbreviation()` in the foreign key index name.

---

### `createDefaultSlugsTableFields`

```php
createDefaultSlugsTableFields(
    Blueprint $table,
    string $tableNameSingular,
    string $tableNamePlural = null
): void
```

Scaffolds a `*_slugs` table:
- `id`, `{model}_id` (foreign key with CASCADE delete), `deleted_at`, timestamps
- `slug` (string), `locale` (string 7, indexed), `active` (boolean)

---

### `createDefaultRelationshipTableFields`

```php
createDefaultRelationshipTableFields(
    Blueprint $table,
    string $table1NameSingular,
    string $table2NameSingular,
    string $table1NamePlural = null,
    string $table2NamePlural = null
): void
```

Scaffolds a many-to-many pivot table with:
- `{table1}_id` and `{table2}_id` as foreign keys (cascade delete/update)
- Composite primary key on both IDs

---

### `createDefaultMorphPivotTableFields`

```php
createDefaultMorphPivotTableFields(
    Blueprint $table,
    string $modelName = null,
    string $tableName = null,
    string $morphedTableName = null
): void
```

Scaffolds a morph pivot table (`*ables`):
- `{model}_id` foreign key → parent table (cascade)
- `{morph_name}_type` + `{morph_name}_id` via `uuidMorphs()` with a named index

---

### `createDefaultRevisionsTableFields`

```php
createDefaultRevisionsTableFields(
    Blueprint $table,
    string $tableNameSingular,
    string $tableNamePlural = null
): void
```

Scaffolds a `*_revisions` table:
- `id`, `{model}_id` (foreign → parent, cascade delete), `user_id` (nullable, set null on delete)
- `created_at`, `updated_at`
- `payload` (JSON)
