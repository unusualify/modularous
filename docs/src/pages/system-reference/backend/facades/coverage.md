---
sidebarPos: 2
sidebarTitle: Coverage
---

# Coverage

**Facade**: `Unusualify\Modularous\Facades\Coverage`  
**Accessor**: `coverage.service`  
**Underlying**: `Unusualify\Modularous\Services\CoverageService`

Parses PHPUnit Clover XML coverage reports and provides analysis, filtering, and export utilities. See [CoverageService](/system-reference/backend/services/coverage-service) for implementation details.

## Methods

### Configuration (Fluent)

| Method | Signature | Description |
|--------|-----------|-------------|
| `setCloverPath` | `(string $path): CoverageService` | Set the path to the Clover XML file |
| `filterByFiles` | `(array $files): CoverageService` | Limit analysis to specific files |
| `setCoverageThreshold` | `(float $threshold): CoverageService` | Set the minimum coverage percentage |
| `skipMagicMethods` | `(bool $skip = true): CoverageService` | Exclude `__*` methods from analysis |
| `skipPrivateMethods` | `(bool $skip = true): CoverageService` | Exclude private methods |
| `skipProtectedMethods` | `(bool $skip = true): CoverageService` | Exclude protected methods |

### Analysis

| Method | Signature | Description |
|--------|-----------|-------------|
| `analyze` | `(): array` | Full coverage analysis of all files |
| `analyzeFile` | `(string $filePath): array` | Analysis for a single file |
| `getMethodCoverage` | `(string $filePath, string $methodName): array\|null` | Coverage data for a specific method |
| `uncovered` | `(array $files = []): array` | Returns methods with 0% coverage |
| `partial` | `(float $threshold = 50.0, array $files = []): array` | Returns methods below `$threshold`% |
| `stats` | `(?array $files = null): array` | Returns aggregate coverage statistics |
| `git` | `(string $baseBranch = 'main'): array` | Returns coverage for files changed vs `$baseBranch` |
| `checkPR` | `(string $baseBranch = 'main', bool $throwOnFailure = false): bool` | CI check — returns false (or throws) if coverage drops |

### Export

| Method | Signature | Description |
|--------|-----------|-------------|
| `json` | `(?array $files = null, bool $prettyPrint = true): string` | Returns coverage as JSON |
| `markdown` | `(?array $files = null): string` | Returns coverage as a Markdown table |
| `html` | `(?array $files = null): string` | Returns coverage as an HTML report |
| `save` | `(string $outputPath, ?array $files = null, string $format = 'json'): bool` | Saves the report to a file |

### Error Handling

| Method | Signature | Description |
|--------|-----------|-------------|
| `getErrors` | `(): array` | Returns any parse errors encountered |
| `hasErrors` | `(): bool` | Whether any errors occurred |

## Usage

```php
use Unusualify\Modularous\Facades\Coverage;

$report = Coverage::setCloverPath(storage_path('coverage.xml'))
    ->skipMagicMethods()
    ->setCoverageThreshold(80.0)
    ->analyze();

// In CI
if (!Coverage::checkPR('main', throwOnFailure: true)) {
    exit(1);
}
```
