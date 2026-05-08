---
sidebarPos: 6
sidebarTitle: VueTestGenerator
---

# VueTestGenerator

**Class**: `Unusualify\Modularity\Generators\VueTestGenerator`  
**Source**: `src/Generators/VueTestGenerator.php`  
**Extends**: [`Generator`](./generator)

Scaffolds a Vitest/Jest test file for a Vue frontend artefact. Supports four test types — component, utility, composable/hook, and Pinia store — each with its own import path convention, naming convention, and stub template.

## Test Types

| Type key | Import dir | Target dir | Naming | Stub | File extension |
|----------|-----------|------------|--------|------|---------------|
| `component` | `components/` | `components` | PascalCase | `tests/vue-component` | `.vue` |
| `util` | `utils/` | `utils` | CamelCase | `tests/vue-util` | `.js` |
| `hook` | `hooks/` | `composables` | CamelCase | `tests/vue-composable` | `.js` |
| `store` | `store/modules/` | `store` | KebabCase | `tests/vue-store` | `.js` |

## Target Path

All Vue test files are written to the package's `vue/test` directory:

```
get_modularity_vendor_path('vue/test')/{target_dir}/{kebab-name}.test.js
```

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$type` | `string` | Active test type key (one of `component`, `util`, `hook`, `store`) |
| `$subImportDir` | `string\|null` | Optional subdirectory appended to the import path |
| `$subTargetDir` | `string\|null` | Optional subdirectory appended to the target path (currently unused in path resolution) |

## Key Methods

### `setType(string $type)`

Sets the active type. Must be one of the keys in `$types`.

### `getTypeImportDir(): string`

Builds the full import path for the stub's `$IMPORT$` replacement:

```
{import_dir}/{subImportDir?}/{ConventionName}.{extension}
```

### `getTypeTargetDir(): string`

Returns the target subdirectory (e.g. `components`, `store`).

### `getTestFileName(): string`

Returns `{kebab-name}.test.js`.

### `generate(): int`

Renders the type-specific stub with four replacements and writes the file:

| Replacement | Value |
|-------------|-------|
| `$STUDLY_NAME$` | StudlyCase name |
| `$CAMEL_CASE$` | camelCase name |
| `$NAMESPACE$` | `test/{target_dir}/{test-file-name}` |
| `$IMPORT$` | Full import path with extension |

## Usage

```php
$generator = new VueTestGenerator('UserCard', $config, $filesystem, $console, $module);
$generator
    ->setType('component')
    ->setSubImportDir('users')  // optional: components/users/UserCard.vue
    ->generate();
// → Creates: vue/test/components/user-card.test.js
```
