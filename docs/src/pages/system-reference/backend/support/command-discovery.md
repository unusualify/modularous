---
sidebarPos: 2
sidebarTitle: CommandDiscovery
---

# CommandDiscovery

`Unusualify\Modularity\Support\CommandDiscovery`

Scans one or more glob paths and returns an array of fully qualified class names (FQCNs) for concrete, instantiable `Illuminate\Console\Command` subclasses. Used by the Modularous service provider to register all artisan commands without a hand-maintained list.

## Static API

```php
CommandDiscovery::discover(array $paths, array $exclude = []): array<string>
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$paths` | `string[]` | Glob patterns — e.g. `__DIR__ . '/../Console/**/*.php'` |
| `$exclude` | `string[]` | Short class names to skip (for legacy compatibility) |

Returns an array of unique FQCNs that pass all filters.

## Filtering Rules

A file is silently skipped if it:

- Declares an `abstract class`, `interface`, `enum`, or `trait`
- Cannot be found in the class map (`class_exists` returns false)
- Is not instantiable (abstract after reflection)
- Does not extend `Illuminate\Console\Command`

Comments and string literals are stripped before checking declarations to avoid false positives in docblocks.

## Example

```php
use Unusualify\Modularity\Support\CommandDiscovery;

$commands = CommandDiscovery::discover([
    __DIR__ . '/Console/Cache/*.php',
    __DIR__ . '/Console/Coverage/*.php',
]);

// Register with Artisan
$this->commands($commands);
```

## How It Works

1. For each glob path, `glob()` expands the pattern to a list of files.
2. The file content is read and stripped of comments/strings.
3. Regex checks exclude non-class declarations.
4. The namespace is extracted from the file and the FQCN is assembled.
5. `ReflectionClass` confirms the class is instantiable and extends `Command`.
