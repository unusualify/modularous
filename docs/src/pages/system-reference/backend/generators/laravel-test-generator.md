---
sidebarPos: 3
sidebarTitle: LaravelTestGenerator
---

# LaravelTestGenerator

**Class**: `Unusualify\Modularity\Generators\LaravelTestGenerator`  
**Source**: `src/Generators/LaravelTestGenerator.php`  
**Extends**: [`Generator`](./generator)

Scaffolds a PHPUnit test file (Unit or Feature) for Modularous backend code. Mirrors the structure of [`VueTestGenerator`](./vue-test-generator) but targets the package's PHP test directory.

## Test Types

| Type key | Import dir | Target dir | Naming | Stub |
|----------|-----------|------------|--------|------|
| `unit` | `Unit/` | `Unit` | PascalCase | `tests/laravel-unit` |
| `feature` | `Feature/` | `Feature` | PascalCase | `tests/laravel-feature` |

## Target Path

All PHP test files are written to the package's `src/Tests` directory:

```
get_modularity_vendor_path('src/Tests')/{target_dir}/{kebab-name}.php
```

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$type` | `string` | Active test type key (`unit` or `feature`) |
| `$subImportDir` | `string\|null` | Optional subdirectory appended to the import path |
| `$subTargetDir` | `string\|null` | Optional subdirectory appended to the target path |

## Key Methods

### `setType(string $type)`

Sets the active type. Must be `'unit'` or `'feature'`.

### `getTypeTargetDir(): string`

Returns `'Unit'` or `'Feature'` based on the active type.

### `getTestFileName(): string`

Returns `{kebab-name}.php`.

### `generate(): int`

Renders the type-specific stub with four replacements and writes the file:

| Replacement | Value |
|-------------|-------|
| `$STUDLY_NAME$` | StudlyCase name |
| `$CAMEL_CASE$` | camelCase name |
| `$NAMESPACE$` | `test/{target_dir}/{test-file-name}` |
| `$IMPORT$` | Import path with `.php` extension |

Files are never overwritten if they already exist.

## Usage

```php
$generator = new LaravelTestGenerator('PostRepository', $config, $filesystem, $console, $module);
$generator
    ->setType('unit')
    ->generate();
// → Creates: src/Tests/Unit/post-repository.php
```
