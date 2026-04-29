---
sidebarPos: 4
sidebarTitle: Coverage PR Check
---

# Coverage PR Check

> Compare coverage against a base branch and optionally fail the process if coverage drops below the threshold — designed as a CI gate.

## Command Information

- **Signature:** `coverage:pr:check [--cloverName=] [--cloverDir=] [--branch=main] [--threshold=0] [--fail]`
- **Category:** Coverage

## Options

| Option | Default | Description |
|--------|---------|-------------|
| `--cloverName` | `clover.xml` | Clover XML file name |
| `--cloverDir` | `storage/app` | Directory containing the Clover file |
| `--branch` | `main` | Base branch to compare against |
| `--threshold` | `0` | Minimum coverage % required |
| `--fail` | `false` | Exit with non-zero code if threshold not met |

## What It Does

The command reads the Clover XML report, resolves coverage for the current working tree, and compares it against the specified base branch. It displays a summary table showing whether coverage increased, decreased, or held steady relative to the base.

When `--fail` is set and the coverage falls below `--threshold`, the command exits with a failure code. This is the intended use in CI pipelines where a failing exit code blocks the merge.

## Examples

### Basic PR check

```bash
php artisan coverage:pr:check
```

### Check against a non-default base branch

```bash
php artisan coverage:pr:check --branch=develop
```

### Enforce 80 % coverage and fail the CI step

```bash
php artisan coverage:pr:check --branch=main --threshold=80 --fail
```

### GitHub Actions example

```yaml
- name: Coverage gate
  run: php artisan coverage:pr:check --branch=main --threshold=75 --fail
```

## Related

- [coverage:analyze](/guide/console/coverage/coverage-analyze) — detailed per-file analysis
- [coverage:report](/guide/console/coverage/coverage-report) — generate full coverage reports
