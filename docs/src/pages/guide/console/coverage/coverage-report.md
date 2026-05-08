---
sidebarPos: 5
sidebarTitle: Coverage Report
---

# Coverage Report

> Generate JSON, Markdown, and/or HTML coverage reports from a Clover XML file.

## Command Information

- **Signature:** `coverage:report [--output=storage/app/coverage] [--format=*] [--git=] [--files=*] [--threshold=0] [--open]`
- **Category:** Coverage

## Options

| Option | Default | Description |
|--------|---------|-------------|
| `--output` | `storage/app/coverage` | Output directory for generated reports |
| `--format` | _(all)_ | Report formats to generate: `json`, `markdown`, `html` (repeatable) |
| `--git` | — | Branch/commit to diff coverage against |
| `--files` | _(all)_ | Filter to specific file paths (repeatable) |
| `--threshold` | `0` | Minimum coverage % — printed in the report header |
| `--open` | `false` | Open the HTML report in the default browser after generation |

## What It Does

Reads the Clover XML report and writes one or more report files to `--output`. If no `--format` is specified all three formats are generated. The HTML report is a standalone, self-contained file suitable for sharing as a build artefact.

When `--open` is passed the command attempts to open `index.html` in the system's default browser.

## Examples

### Generate all formats

```bash
php artisan coverage:report
```

### Generate only Markdown (e.g. for GitHub summaries)

```bash
php artisan coverage:report --format=markdown
```

### Generate HTML and open immediately

```bash
php artisan coverage:report --format=html --open
```

### Write to a custom directory

```bash
php artisan coverage:report --output=public/coverage
```

### Include a threshold in the report and diff vs. branch

```bash
php artisan coverage:report --threshold=80 --git=main
```

## Related

- [coverage:analyze](/guide/console/coverage/coverage-analyze) — inline terminal analysis
- [coverage:pr:check](/guide/console/coverage/coverage-pr-check) — CI threshold gate
