---
sidebarPos: 4
sidebarTitle: Decomposers
---

# Decomposers

The `Support\Decomposers` sub-namespace contains three parser classes used by the code generators to convert compact CLI-style definition strings into structured PHP arrays.

---

## ModelRelationParser

`Unusualify\Modularous\Support\Decomposers\ModelRelationParser`

Parses a relation definition string (as entered in `make:model --relations`) into an array of Eloquent relationship descriptors, and can render the resulting `public function …()` method stubs.

### Supported Relation Types

`belongsTo`, `hasOne`, `hasMany`, `hasOneThrough`, `hasManyThrough`, `belongsToMany`, `morphTo`, `morphOne`, `morphToMany`

### Input Format

Each relation is expressed as a colon-separated string:

```
company:belongsTo
tags:belongsToMany:tag
commentable:morphTo
```

### Key Methods

| Method | Description |
|--------|-------------|
| `parse(string $relations): array` | Parse the string and return an array of relation descriptors |
| `toArray(): array` | Call `parse()` on the stored relations |
| `render(): string` | Render all relations as PHP method stubs |

### Example

```php
use Unusualify\Modularous\Support\Decomposers\ModelRelationParser;

$parser = new ModelRelationParser('company:belongsTo,tags:belongsToMany:tag');
$rendered = $parser->render();
// outputs public function company() { return $this->belongsTo(Company::class); } ...
```

---

## SchemaParser (Decomposers)

`Unusualify\Modularous\Support\Decomposers\SchemaParser`

Parses a field-definition string (as entered in `make:model --fields`) into an array of input schema descriptors consumed by hydrate and Vue input generators.

### Input Format

Fields are comma-separated, each with `name:type[:modifier…]` syntax:

```
title:string,body:textarea,published_at:datetime:nullable,status:select
```

### Key Methods

| Method | Description |
|--------|-------------|
| `parse(string $schema): array` | Parse the string and return field descriptors |
| `toArray(): array` | Call `parse()` on the stored schema |
| `render(): string` | Render input schema stubs for use in Hydrate files |

---

## ValidatorParser

`Unusualify\Modularous\Support\Decomposers\ValidatorParser`

Parses a validation-rules string (as entered in `make:model --rules`) into a `['field' => 'rules']` array suitable for pasting into a `StoreRequest` / `UpdateRequest`.

### Input Format

Rules are `&`-separated, each field expressed as `field=rule1|rule2`:

```
title=required|string|max:255&body=nullable|string&published_at=nullable|date
```

### Key Methods

| Method | Description |
|--------|-------------|
| `parse(mixed $rules): array` | Parse and return `['field' => 'rules_string']` |
| `toArray(): array` | Call `parse()` on the stored rules |
| `toReplacement(): string` | Render the array as a PHP `array_export` string with correct indentation for insertion into a stub |
| `getFields(): array` | Return the raw `&`-split field tokens |

### Example

```php
use Unusualify\Modularous\Support\Decomposers\ValidatorParser;

$parser = new ValidatorParser('title=required|string|max:255&body=nullable|string');
$rules  = $parser->toArray();
// ['title' => 'required|string|max:255', 'body' => 'nullable|string']
```

---

## Related

- [Migrations\SchemaParser](./migrations-schema-parser) — renders migration `$table->…` code from the same schema string format
- [Generators](/system-reference/backend/generators/overview) — consume all three parsers during scaffolding
