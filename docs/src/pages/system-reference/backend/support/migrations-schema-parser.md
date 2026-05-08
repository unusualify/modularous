---
sidebarPos: 8
sidebarTitle: Migrations SchemaParser
---

# Migrations\SchemaParser

`Nwidart\Modules\Support\Migrations\SchemaParser`

Converts a compact field-definition string into the `$table->…` PHP snippets written inside migration `up()` / `down()` methods. Used exclusively by Modularous migration generator stubs.

::: info Namespace note
This class lives under the `Nwidart\Modules` namespace because Modularous extends nwidart/laravel-modules. The file is at `src/Support/Migrations/SchemaParser.php`.
:::

## Input Format

A schema string is a comma-separated list of `column:type[:modifier…]` tokens:

```
name:string,description:text,published_at:timestamp:nullable,company:belongsTo,settings:json
```

Special column shorthand:

| Token | Expands to |
|-------|-----------|
| `remember_token` | `->rememberToken()` |
| `soft_delete` | `->softDeletes()` |
| `column:morphTo` | Two columns: `{column}_type:string:nullable` + `{column}_id:unsignedBigInteger:nullable` |
| `column:belongsToMany` | Pivot table — no column emitted in this migration |
| `column:hasOne` | No column emitted in this migration |

## API

### `parse(string $schema): array`

Parse the schema string and return `['column' => ['type', 'modifier1', …]]`.

```php
$parser = new SchemaParser('title:string,body:text:nullable');
$parsed = $parser->parse('title:string,body:text:nullable');
// ['title' => ['string'], 'body' => ['text', 'nullable']]
```

### `toArray(): array`

Equivalent to `parse($this->schema)`.

### `render() / up(): string`

Render the `$table->…;` PHP lines for the `up()` migration method.

```php
$parser = new SchemaParser('title:string,company:belongsTo,published_at:timestamp:nullable');
echo $parser->render();
```

Output:

```php
			$table->string('title');
			$table->foreignId('company_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
			$table->timestamp('published_at')->nullable();
```

### `down(): string`

Render the `$table->dropColumn(…);` lines for the `down()` method.

### `createField(string $column, array $attributes, string $type = 'add'): string`

Build a single field line. `$type` can be `'add'` or `'remove'`.

## Custom Attributes

Two hardcoded shorthand tokens:

| Column name | Expands to |
|-------------|-----------|
| `remember_token` | `->rememberToken()` |
| `soft_delete` | `->softDeletes()` |

## Relation Handling

| Relation type | `up()` behaviour | `down()` behaviour |
|---------------|------------------|--------------------|
| `belongsTo` | `foreignId('{col}_id')->constrained()->…` | `dropColumn('{col}_id')` |
| `morphTo` | Two columns: `_type` (string) + `_id` (unsignedBigInteger) | Drop both |
| `belongsToMany` | Skipped (pivot handled separately) | Skipped |
| `hasOne` | Skipped (FK on the related table) | Skipped |

## Related

- [Decomposers\SchemaParser](./decomposers) — parses the same string format for Hydrate/Vue input generation
- [Generators](/system-reference/backend/generators/overview) — passes the schema string to both parsers during `make:model`
