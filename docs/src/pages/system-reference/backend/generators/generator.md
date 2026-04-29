---
sidebarPos: 2
sidebarTitle: Generator
---

# Generator

**Class**: `Unusualify\Modularity\Generators\Generator`  
**Source**: `src/Generators/Generator.php`  
**Extends**: `Nwidart\Modules\Generators\Generator`  
**Uses**: `ReplacementTrait`

Abstract base class for all Modularous generators. Defines the shared property set, constructor signature, module-resolution helpers, and config path utilities that every concrete generator inherits.

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$name` | `string` | — | The route/entity name being generated (StudlyCase) |
| `$app` | `Container` | — | The Laravel service container instance |
| `$config` | `Config` | `null` | Laravel config repository |
| `$filesystem` | `Filesystem` | `null` | Laravel filesystem instance |
| `$console` | `Console` | `null` | The Artisan command instance (for output and sub-calls) |
| `$module` | `Module` | `null` | The nwidart Module instance being targeted |
| `$moduleName` | `string` | — | Name of the module, derived from `$module` |
| `$route` | `string` | — | Route name (may differ from entity name) |
| `$force` | `bool` | `false` | When true, overwrite existing files |
| `$fix` | `bool` | `false` | When true, repair/patch instead of creating from scratch |
| `$test` | `bool` | `false` | When true, run in dry-run mode — prints what would be created |

## Constructor

```php
public function __construct(
    string $name,
    ?Config $config = null,
    ?Filesystem $filesystem = null,
    ?Console $console = null,
    ?Module $module = null
)
```

All dependencies are optional to allow incremental construction via setters. When `$module` is supplied at construction time, `$moduleName` is set automatically.

## Key Methods

### Module resolution

```php
$generator->setModule('Posts'); // resolves by name via Modularous::find()
$generator->getModule();        // returns Module instance
```

`setModule()` calls `Modularity::find($module)` and also re-initialises any module-dependent state (e.g. translation paths in `RouteGenerator`).

### Config helpers

| Method | Description |
|--------|-------------|
| `generatorConfig($key)` | Returns a `GeneratorPath` object for the generator config key (e.g. `'repository'`, `'route-controller'`) |
| `getModularityGeneratorConfig($key)` | Raw config value from `modularity.paths.generator.{key}` |
| `getTargetPath()` | Returns `$module->getPath()` or `false` if no module is set |

### Fluent setters

All properties have matching `set*()/get*()` pairs: `setName`, `setConfig`, `setFilesystem`, `setConsole`, `setRoute`, `setForce`, `setFix`, `setTest`.

## Abstract Method

```php
abstract protected function generate(): int;
```

Every concrete generator must implement `generate()`. Return `0` for success, `E_ERROR` on failure.

## Usage

Never instantiate `Generator` directly. Use a concrete subclass:

```php
$generator = new RouteGenerator(
    name: 'Post',
    config: app('config'),
    filesystem: app('files'),
    console: $this, // Artisan command instance
    module: Modularity::find('Blog'),
);

$generator->setSchema('title:string,body:text')
          ->setForce(false)
          ->generate();
```
