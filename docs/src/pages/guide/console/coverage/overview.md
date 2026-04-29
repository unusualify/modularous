---
sidebarPos: 6
sidebarTitle: Overview
sidebarGroupTitle: Coverage
---

# Coverage Commands

The Coverage command group wraps the `CoverageService` / `Coverage` facade to analyse Clover XML reports, generate reports, check PR thresholds, watch live coverage changes, and AI-generate missing tests.

| Command | Description |
|---------|-------------|
| [coverage:analyze](/guide/console/coverage/coverage-analyze) | Analyse a Clover XML report and display per-file coverage |
| [coverage:pr:check](/guide/console/coverage/coverage-pr-check) | Compare coverage against a base branch and fail if below threshold |
| [coverage:report](/guide/console/coverage/coverage-report) | Generate JSON, Markdown, or HTML coverage reports |
| [coverage:generate-tests](/guide/console/coverage/coverage-generate-tests) | Scaffold missing tests — optionally via AI provider |
| [coverage:watch](/guide/console/coverage/coverage-watch) | Poll a Clover file and display diffs as coverage changes |

## Prerequisites

All commands require a Clover XML file produced by PHPUnit or Pest:

```bash
php artisan test --coverage-clover=storage/app/clover.xml
```

The default file name and directory can be overridden per command with `--cloverName` and `--cloverDir`.

## Common Workflows

### Generate a local coverage report

```bash
php artisan test --coverage-clover=storage/app/clover.xml
php artisan modularity:coverage:analyze        # quick per-file summary
php artisan modularity:coverage:report         # JSON / markdown / HTML rendering
```

### Gate a pull request on coverage

```bash
php artisan modularity:coverage:pr:check --threshold=80 --base=main
```

Fails with a non-zero exit code when PR coverage drops below the threshold — wire it into CI.

### Scaffold missing tests (optionally AI-assisted)

```bash
php artisan modularity:coverage:generate-tests
```

Creates PHPUnit/Pest test stubs for uncovered methods. When an AI provider is configured, `--ai` fills in assertions; otherwise the stubs are empty scaffolds.

### Watch coverage during TDD

```bash
php artisan modularity:coverage:watch
```

Polls the Clover file and prints a diff whenever coverage changes — run alongside your test runner for fast feedback.

## Related

- [CoverageService](/system-reference/backend/services/overview) — underlying service
- [Coverage facade](/system-reference/backend/facades/overview) — programmatic access
