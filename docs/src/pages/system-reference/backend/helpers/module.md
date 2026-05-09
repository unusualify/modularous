---
sidebarPos: 14
sidebarTitle: module
---

# module

**File**: `src/Helpers/module.php`

Module context, configuration, permission, and debug helpers. This is the largest helper file for module-aware code — used heavily in service providers, console commands, and module config files.

## Module Context

Functions that resolve the "current module" by tracing the PHP call stack.

### `modularousBaseKey`

```php
modularousBaseKey(string $notation = null): string
```

Returns the root config key for Modularous (default: `modularous`). If `MODULAROUS_BASE_NAME` is set in `.env`, it is used instead. Optional `$notation` is appended with a `.` separator.

```php
modularousBaseKey('locale'); // → 'modularous.locale'
```

---

### `curtModule`

```php
curtModule(string $file = null): Module
```

Resolves and returns the current module instance by tracing the file path in the call stack to extract a module name, then calling `Modularous::find()`.

---

### `curtModuleName`

```php
curtModuleName(string $file = null): string
```

Extracts the module name from the call stack by matching the `Modules/{ModuleName}` pattern. Throws `ModularousException` if it cannot be determined.

---

### `curtModuleUrlPrefix` / `curtModuleRouteNamePrefix`

```php
curtModuleUrlPrefix(string $file = null): string
curtModuleRouteNamePrefix(string $file = null): string
```

Returns the URL prefix / route name prefix of the current module.

---

### `curtModuleStudlyName` / `curtModuleLowerName` / `curtModuleSnakeName`

```php
curtModuleStudlyName(string $file = null): string
curtModuleLowerName(string $file = null): string
curtModuleSnakeName(string $file = null): string
```

Return the current module name in the requested casing.

---

## Trait / Class Inspection

### `classUsesDeep`

```php
classUsesDeep(mixed $class, bool $autoload = true): array
```

Returns all traits used by a class and all of its parent classes — including traits used by other traits (recursive). Returns a flat, unique array of trait names.

---

### `classHasTrait`

```php
classHasTrait(mixed $class, string $trait): bool
```

Returns `true` if `$class` (or any parent/trait in the hierarchy) uses `$trait`. Uses `classUsesDeep` internally.

---

## Route Helpers

### `moduleRoute`

```php
moduleRoute(
    string $moduleName,
    string $prefix,
    string $action = '',
    array $parameters = [],
    bool $absolute = true,
    bool $singleton = false
): string
```

Generates a full URL for a module route. Automatically appends `:id` for edit/show/update/destroy/duplicate actions on non-singleton resources. Throws `ModularousException` with full context on route generation failure.

---

### `modularousRoute`

```php
modularousRoute(
    string $route,
    string $prefix,
    string $action = '',
    array $parameters = [],
    bool $absolute = true
): string
```

Similar to `moduleRoute` but for Modularous built-in routes (not module-specific).

---

## Trait Options

### `getModularousTraits` / `activeModularousTraits` / `modularousTraitOptions`

```php
getModularousTraits(): array
activeModularousTraits(array $traitOptions): Collection
modularousTraitOptions(bool $asSignature = false): array|string
```

Read the registered trait list from `modularous.traits` config. `modularousTraitOptions` can return either a plain array or a formatted Symfony `InputOption` signature string for `make:*` commands.

---

## Configuration

### `modularousConfig`

```php
modularousConfig(string $notation = null, mixed $default = ''): mixed
```

Shorthand for `config(modularousBaseKey($notation), $default)`. The most-used helper across the entire codebase.

---

## Permissions

### `formatPermissionName`

```php
formatPermissionName(string $routeName, string $permissionType): string
```

Returns a kebab-cased permission name: `route-name_permission-type`.

### `formatPermissionRecord`

```php
formatPermissionRecord(string $routeName, string $permissionType, string $guardName): array
```

Returns `['name' => ..., 'guard_name' => ...]` for a Spatie Permission record.

### `routePermissionRecords`

```php
routePermissionRecords(string $routeName, string $guardName, array $cases = null): array
```

Returns all permission records for a route across all `Permission` enum cases.

### `permissionRecordsFromRoutes`

```php
permissionRecordsFromRoutes(array $routes, string $guardName): array
```

Returns permission records for multiple routes combined.

---

## Debug / Utility

### `ifdd`

```php
ifdd(bool $condition, mixed ...$vars): void
```

Conditional `dd()`. Dumps all `$vars` and exits only if `$condition` is true. Uses `VarDumper::dump` directly.

---

### `exceptionalRunningInConsole`

```php
exceptionalRunningInConsole(): bool
```

Returns `true` if the app is NOT running specific module-generation console commands. Used to skip expensive boot steps when running `make:module`, `make:route`, etc.

---

### `backtrace_formatter` / `backtrace_formatted`

```php
backtrace_formatter(array $carry, array $item): array
backtrace_formatted(): array
```

`backtrace_formatted()` returns a clean associative array of the current call stack: `['file' => ['line' => N, 'function' => 'fn']]`.

---

### `benchmark`

```php
benchmark(
    callable $callback,
    string $label = null,
    bool $die = false,
    string $unit = 'milliseconds',
    string &$elapsedString = null
): mixed
```

Wraps `$callback` with timing. Logs to the `modularous-benchmark` channel:
- At `emergency` level if elapsed exceeds `benchmark_emergency_time` config (default 1000ms)
- At `debug` level if `benchmark_log_level` is `'debug'`

Set `$die = true` to throw immediately with the elapsed time (useful for profiling in development). Set `$label` to identify the operation in logs. Only active when `benchmark_enabled` config is `true`.

---

### `mergeConfigFrom`

```php
mergeConfigFrom(string $path, string $key): void
```

Loads a PHP config file from `$path` and deep-merges it (via `array_merge_recursive_preserve`) into the existing `$key` config value. Overrides Laravel's default `mergeConfigFrom` which uses a shallow `array_merge`.

### `findParentRoute`

```php
findParentRoute(array $config): array
```

Returns the first route in a module config that has `'parent' => true`.
