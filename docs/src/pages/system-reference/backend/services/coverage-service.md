---
sidebarPos: 12
sidebarTitle: CoverageService
---

# CoverageService

**File**: `src/Services/CoverageService.php`  
**Bound as**: `coverage.service`  
**Config**: `config/modularity-coverage.php`

`CoverageService` is a developer tool that parses PHPUnit Clover XML coverage reports and exposes methods for analysing uncovered code, generating reports (JSON / Markdown / HTML), and running PR coverage checks.

## Configuration

```php
// config/modularity-coverage.php
return [
    'clover_dir'  => base_path(),       // directory containing the Clover XML file
    'clover_name' => 'clover.xml',      // Clover file name
];
```

## Instantiation

```php
// Via service container (uses config defaults)
$coverage = app('coverage.service');

// Static factory with custom path
$coverage = CoverageService::make('/path/to/project', 'custom-clover.xml');

// Singleton (cached)
$coverage = CoverageService::instance();
```

## Fluent Configuration

| Method | Description |
|--------|-------------|
| `setCloverPath(string $path)` | Override the Clover directory |
| `setCloverName(string $name)` | Override the Clover file name |
| `filterByFiles(array $files)` | Restrict analysis to specific file paths |
| `setCoverageThreshold(float $threshold)` | Only report methods below this coverage % |
| `skipMagicMethods(bool $skip)` | Exclude `__construct`, `__get`, etc. |
| `skipPrivateMethods(bool $skip)` | Exclude private methods |
| `skipProtectedMethods(bool $skip)` | Exclude protected methods |

All fluent methods return `$this` for chaining.

## Analysis Methods

| Method | Description |
|--------|-------------|
| `analyze(): array` | Full analysis — returns array of under-covered methods |
| `analyzeFile(string $filePath): array` | Analyse a single file |
| `getMethodCoverage(string $filePath, string $methodName): ?array` | Coverage data for one method |
| `uncovered(array $files = []): array` | Methods with 0% coverage |
| `partial(float $threshold = 50.0, array $files = []): array` | Methods below threshold |
| `stats(?array $files = null): array` | Summary statistics |
| `git(string $baseBranch = '0.x'): array` | Analyse only files changed vs base branch |
| `checkPR(string $baseBranch = 'main', bool $throwOnFailure = false): bool` | Returns `true` if all changed files meet coverage requirements |

## Report Generation

| Method | Output |
|--------|--------|
| `json(?array $files, bool $prettyPrint = true): string` | JSON report string |
| `markdown(?array $files): string` | Markdown report string |
| `html(?array $files): string` | Self-contained HTML report string |
| `save(string $outputPath, ?array $files, string $format = 'json'): bool` | Save report to file (`json`, `markdown`, `html`) |

## Example

```php
// Find all uncovered methods
$uncovered = CoverageService::make()->uncovered();

// Generate and save a Markdown report
CoverageService::make()
    ->filterByFiles(['src/Services/Connector.php'])
    ->save(storage_path('coverage-report.md'), format: 'markdown');

// Fail a CI step if changed files are not covered
CoverageService::make()->checkPR('main', throwOnFailure: true);
```

## Notes

- `getErrors()` / `hasErrors()` expose any XML parsing errors from the underlying `CoverageAnalyzer`.
- `git()` uses `shell_exec('git diff --name-only ...')` to discover changed files; the `cloverDir` must be a git repository.
- The singleton (`instance()`) is reset with `clearInstance()`, useful in tests.
