---
sidebarPos: 5
sidebarTitle: StubsGenerator
---

# StubsGenerator

**Class**: `Unusualify\Modularous\Generators\StubsGenerator`  
**Source**: `src/Generators/StubsGenerator.php`  
**Extends**: [`Generator`](./generator)

A lighter variant of [`RouteGenerator`](./route-generator) that only regenerates stub-based files. It does not touch the module config, run migrations, create models, or seed permissions. Used by fix/patch commands to refresh specific generated files (controllers, Vue pages, routes) without disrupting the rest of the route.

## Additional Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$onlyStubs` | `array` | `[]` | When set, only these stub keys will be (re)written |
| `$exceptStubs` | `array` | `[]` | When set, all stubs except these will be (re)written |

## Methods

### `setOnly(array $only)`

Restricts stub generation to the listed stub keys. Only meaningful when `$fix = true`.

```php
$generator->setOnly(['controller', 'vue-index']);
```

### `setExcept(array $except)`

Regenerates all stubs **except** the listed stub keys. Only meaningful when `$fix = true`.

```php
$generator->setExcept(['migration', 'model']);
```

### `generate(): int`

Validates that the route config exists, then delegates entirely to `generateFiles()`. No config writes, no resource commands, no migrations.

### `generateFiles()`

Iterates the stubs defined in `modularous.stubs.files` and writes each one if:
- The file does not yet exist, **or**
- `forcibleStub($stub)` returns `true`

### `forcibleStub(string $stub): bool`

Determines whether an existing file should be overwritten:

| State | Behaviour |
|-------|-----------|
| `$force = true` | Always overwrite every stub |
| `$fix = true` + `$onlyStubs` set | Overwrite only stubs in the `onlyStubs` list |
| `$fix = true` + `$exceptStubs` set | Overwrite all stubs not in the `exceptStubs` list |
| Neither flag | Never overwrite existing files |

## Use Case

```php
// Regenerate only the controller and Vue index page for the 'Post' route
$generator = new StubsGenerator('Post', $config, $filesystem, $console, $module);
$generator
    ->setFix(true)
    ->setOnly(['controller', 'vue-index'])
    ->generate();
```

This is the generator used when running `modularous:fix:stubs` — it lets developers refresh auto-generated boilerplate without losing manual edits to files like the migration or model.
