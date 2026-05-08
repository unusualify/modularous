---
sidebarPos: 4
sidebarTitle: CoverageServiceProvider
---

# CoverageServiceProvider

**Class**: `Unusualify\Modularity\Providers\CoverageServiceProvider`  
**Source**: `src/Providers/CoverageServiceProvider.php`  
**Extends**: `Illuminate\Support\ServiceProvider`

Registers the code-coverage analysis infrastructure. Exposes a low-level `CoverageAnalyzer` for parsing Clover XML reports and a high-level `CoverageService` singleton for querying coverage data.

## `register()`

### `coverage.analyzer` (transient)

A new `CoverageAnalyzer` instance is created for each resolution. Accepts optional construction parameters:

```php
app('coverage.analyzer', [
    'cloverDir'  => '/path/to/clover',
    'cloverName' => 'coverage-clover.xml',
]);
```

Falls back to `modularity-coverage.clover_dir` and `modularity-coverage.clover_name` config values, then to the package vendor path.

### `coverage.service` (singleton)

A single `CoverageService` instance shared across the application. Used by the `Coverage` facade.

```php
app('coverage.service')->getCoverage(); // CoverageService facade
```

### Alias

`CoverageService::class` is aliased to `coverage.service`, so type-hinted injection works:

```php
public function __construct(CoverageService $coverage) { ... }
```

## `boot()`

### Config publishing

```bash
php artisan vendor:publish --tag=modularity-coverage-config
```

Publishes `config/coverage.php` → `config/modularity-coverage.php`.

### Console commands

When running in the console, discovers and registers all commands from `src/Console/Coverage/`:

```php
CommandDiscovery::discover([__DIR__ . '/../Console/Coverage/*.php'])
```

## `provides()`

Returns `['coverage.analyzer', 'coverage.service', CoverageService::class]`, allowing Laravel to defer loading this provider until one of these bindings is requested.

## Configuration

```php
// config/modularity-coverage.php
return [
    'clover_dir'  => base_path(),          // directory containing the Clover XML file
    'clover_name' => 'coverage-clover.xml', // filename of the Clover report
];
```
