---
sidebarPos: 8
sidebarTitle: format
---

# format

**File**: `src/Helpers/format.php`

The largest helper file — 45+ functions covering string casing, class reflection, code generation, data access, and closure transformation utilities.

## String Casing

| Function | Signature | Description |
|----------|-----------|-------------|
| `lowerName` | `(string $name): string` | Lowercases the name |
| `studlyName` | `(string $name): string` | Converts to `StudlyCase` via `Str::studly()` |
| `camelCase` | `(string $name): string` | Converts to `camelCase` via `Str::camel()` |
| `kebabCase` | `(string $name): string` | Converts to `kebab-case` via `Str::kebab()` |
| `snakeCase` | `(string $name): string` | Converts to `snake_case` via `Str::snake()` |
| `pluralize` | `(string $name): string` | Returns plural form via `Str::plural()` |
| `singularize` | `(string $name): string` | Returns singular form via `Str::singular()` |
| `headline` | `(string $name): string` | Converts to `Headline Case` via `Str::headline()` |
| `tableName` | `(string $name): string` | Converts to plural snake_case table name |
| `camelCaseToWords` | `(string $name): string` | Splits `camelCase` into space-separated words |
| `is_plural` | `(string $name): bool` | Returns whether the string is already plural |

## Foreign Key / Morph Naming

| Function | Signature | Description |
|----------|-----------|-------------|
| `makeForeignKey` | `(string $model): string` | `user` → `user_id` |
| `makeMorphToName` | `(string $model): string` | Returns morph-to relation name |
| `makeMorphName` | `(string $model): string` | Returns morph type name (without `_type`) |
| `makeMorphForeignKey` | `(string $model): string` | Returns `{model}_id` for polymorphic pivot |
| `makeMorphForeignType` | `(string $model): string` | Returns `{model}_type` for polymorphic pivot |
| `makeMorphToMethodName` | `(string $model): string` | Returns `morphTo` method name |
| `makeMorphPivotTableName` | `(string $model): string` | Returns pivot table name for a morph pivot |
| `getMorphModelName` | `(string $tableName): string` | Strips `able`/`ables` suffix to get model name |

## Class / Namespace Reflection

| Function | Signature | Description |
|----------|-----------|-------------|
| `abbreviation` | `(string $name): string` | Builds an abbreviation from underscored words (e.g. `user_profile` → `up`) |
| `get_class_short_name` | `(string $class): string` | Returns the unqualified class name from a FQCN |
| `class_resolution` | `(string $class): string` | Resolves a class alias or short name to its FQCN |
| `class_namespace` | `(string $class): string` | Extracts the namespace from a FQCN |
| `fileTrace` | `(string $pattern): string` | Searches `debug_backtrace()` for a file path matching the regex `$pattern` |

## Code Generation

These helpers write PHP source fragments used by `make:*` console commands.

| Function | Signature | Description |
|----------|-----------|-------------|
| `get_file_string` | `(string $path): string` | Reads a stub file |
| `replace_curly_braces` | `(string $stub, array $replacements): string` | Replaces `{{KEY}}` tokens in a stub string |
| `indent` | `(string $content, int $level = 1): string` | Indents content by `$level × 4` spaces |
| `comment_string` | `(string $text): string` | Wraps text in a `/* ... */` comment block |
| `method_string` | `(string $name, string $body, ...): string` | Generates a PHP method declaration |
| `attribute_string` | `(string $name, mixed $value): string` | Generates a PHP class attribute line |
| `concatenate_path` | `(string ...$parts): string` | Joins path segments with `/`, deduplicating slashes |
| `concatenate_namespace` | `(string ...$parts): string` | Joins namespace segments with `\\` |
| `get_file_class` | `(string $path): string` | Extracts the class name from a PHP file |

## Validation / Rules

| Function | Signature | Description |
|----------|-----------|-------------|
| `parseRulesSchema` | `(string|array $rules): array` | Normalizes Laravel validation rules to array form |
| `formatRulesSchema` | `(array $rules): string` | Converts rules array back to pipe-delimited string |

## Data Access

| Function | Signature | Description |
|----------|-----------|-------------|
| `getValueOrNull` | `(array $data, string $key): mixed` | Returns `$data[$key]` or `null` if missing |
| `tryOperation` | `(callable $fn, mixed $default = null): mixed` | Executes `$fn`, returns `$default` on any `Throwable` |
| `data_get_with_dot_keys` | `(array $data, string $key): mixed` | Like `data_get()` but treats literal dot-keys as single keys first |
| `data_set_with_dot_keys` | `(array &$data, string $key, mixed $value): void` | Like `data_set()` but handles literal dot-keys |
| `wrapImplode` | `(string $separator, array $array, string $prepend, string $append): string` | Implodes array with optional prefix/suffix |

## Relationship Map

| Function | Signature | Description |
|----------|-----------|-------------|
| `laravelRelationshipMap` | `(): array` | Returns the cached Eloquent relationship type map |
| `saveLaravelRelationshipMap` | `(array $map): void` | Persists the relationship map to the modularity cache |

## Routing / Display

| Function | Signature | Description |
|----------|-----------|-------------|
| `modelShowFormat` | `(mixed $model): string` | Returns the display string for a model instance |
| `nestedRouteNameFormat` | `(string $routeName): string` | Formats a nested route name for display |

## User

| Function | Signature | Description |
|----------|-----------|-------------|
| `get_user_profile` | `(): array` | Returns the authenticated user's profile data array |
| `name_surname_resolver` | `(string $fullName): array` | Splits a full name string into `['name' => ..., 'surname' => ...]` |

## Variable Replacement

| Function | Signature | Description |
|----------|-----------|-------------|
| `replace_variables_from_haystack` | `(string $haystack, array $vars): string` | Replaces `{KEY}` tokens using `$vars` |
| `extract_schema_extensions` | `(array $schema): array` | Extracts `ext` values from a form schema |
| `transform_closure_value` | `(mixed $value, bool $forceArray = false): mixed` | If `$value` is a `Closure`, calls it and returns the result |
| `transform_closure_values` | `(array $data, bool $forceArray = false): array` | Maps `transform_closure_value` over an array |
