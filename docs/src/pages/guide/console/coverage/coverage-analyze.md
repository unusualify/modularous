---
sidebarPos: 2
sidebarTitle: Coverage Analyze
---

# Coverage Analyze

> Analyse a Clover XML coverage report and display per-file results, optionally comparing against a git branch.

## Command Information

- **Signature:** `coverage:analyze [--cloverName=] [--cloverDir=] [--files=*] [--threshold=0] [--git=] [--skip-magic] [--format=table]`
- **Category:** Coverage

## Options

| Option | Default | Description |
|--------|---------|-------------|
| `--cloverName` | `clover.xml` | Clover XML file name |
| `--cloverDir` | `storage/app` | Directory containing the Clover file |
| `--files` | _(all)_ | Filter to specific file paths (repeatable) |
| `--threshold` | `0` | Minimum coverage % to consider passing |
| `--git` | — | Branch/commit to diff coverage against |
| `--skip-magic` | `false` | Exclude magic methods from analysis |
| `--format` | `table` | Output format: `table`, `json`, or `list` |

## Examples

### Basic analysis

```bash
php artisan coverage:analyze
```

### Filter to specific files

```bash
php artisan coverage:analyze --files=app/Models/User.php --files=app/Services/PaymentService.php
```

### Set a minimum threshold

```bash
php artisan coverage:analyze --threshold=80
```

### Compare against another branch

```bash
php artisan coverage:analyze --git=main
```

### Output as JSON

```bash
php artisan coverage:analyze --format=json
```

## Related

- [coverage:report](/guide/console/coverage/coverage-report) — generate full HTML/Markdown/JSON reports
- [coverage:pr:check](/guide/console/coverage/coverage-pr-check) — CI gate that fails on threshold breach
