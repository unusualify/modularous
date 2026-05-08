---
sidebarPos: 3
sidebarTitle: CoverageAnalyzer
---

# CoverageAnalyzer

`Unusualify\Modularity\Support\CoverageAnalyzer`

Parses a PHPUnit Clover XML report and identifies methods with low or zero coverage. This is the core engine behind all five [Coverage commands](/guide/console/coverage/overview).

## Constructor

```php
new CoverageAnalyzer(string $cloverDir, string $cloverName)
```

Throws `InvalidArgumentException` if the file does not exist or is not readable.

## Fluent Configuration

| Method | Default | Description |
|--------|---------|-------------|
| `filterByFiles(array $files)` | _(all)_ | Restrict analysis to specific file paths |
| `setCoverageThreshold(float $threshold)` | `0.0` | Only return methods below this coverage % |
| `skipMagicMethods(bool $skip = true)` | `true` | Exclude `__construct`, `__toString`, etc. |
| `skipPrivateMethods(bool $skip = true)` | `false` | Exclude private methods |
| `skipProtectedMethods(bool $skip = true)` | `false` | Exclude protected methods |

All configuration methods return `$this` for chaining.

## Methods

### `analyze(): array`

Analyze all files (or the filtered set) and return an array of method records for every method below the threshold.

Each record contains:

```php
[
    'class'           => 'App\\Models\\User',
    'method'          => 'getFullName',
    'file'            => '/var/www/app/Models/User.php',
    'line'            => 42,
    'visibility'      => 'public',
    'complexity'      => 3,
    'crap'            => 4.5,
    'coverage'        => 0.0,      // percentage
    'execution_count' => 0,        // number of times method was called in tests
    'lines'           => [
        'total'    => 5,
        'covered'  => 0,
        'uncovered'=> 5,
        'details'  => [/* per-statement line records */],
    ],
]
```

### `analyzeFile(string $filePath): array`

Analyze a single file path directly.

### `getMethodCoverage(string $filePath, string $methodName): ?array`

Return coverage details for one specific method, or `null` if not found.

### `getOverallStatistics(): array`

Return project-level aggregates from the Clover `<metrics>` element:

```php
[
    'files'                       => 42,
    'methods'                     => 380,
    'covered_methods'             => 290,
    'method_coverage_percent'     => 76.32,
    'statement_coverage_percent'  => 81.5,
    // ...
]
```

### `getErrors(): array` / `hasErrors(): bool`

Retrieve any non-fatal parsing warnings.

## Example

```php
use Unusualify\Modularity\Support\CoverageAnalyzer;

$analyzer = (new CoverageAnalyzer('storage/app', 'clover.xml'))
    ->setCoverageThreshold(80.0)
    ->skipMagicMethods();

$uncovered = $analyzer->analyze();

foreach ($uncovered as $method) {
    echo "{$method['class']}::{$method['method']} — {$method['coverage']}%\n";
}

$stats = $analyzer->getOverallStatistics();
echo "Overall method coverage: {$stats['method_coverage_percent']}%\n";
```

## Related

- [coverage:analyze](/guide/console/coverage/coverage-analyze) — CLI wrapper
- [coverage:generate-tests](/guide/console/coverage/coverage-generate-tests) — uses `CoverageAnalyzer` to find candidates for test generation
