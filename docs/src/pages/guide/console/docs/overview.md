---
sidebarPos: 8
sidebarTitle: Overview
sidebarGroupTitle: Docs
---

# Docs Commands

Generate and audit the Modularous documentation. These commands correspond to the PHP classes under `src/Console/Docs/`.

| Command | Signature | Description |
|---------|-----------|-------------|
| [docs:audit](./docs-audit) | `modularous:docs:audit` | Audit source files against documentation pages and report gaps |
| [generate-command-docs](./generate-command-docs) | `modularous:generate:command:docs` | Auto-generate Markdown reference pages for all registered `modularous:*` commands |

## Common Workflows

### Check documentation coverage

```bash
php artisan modularous:docs:audit
```

Prints a per-section table showing how many source files are documented and lists what's missing. See [docs:audit](./docs-audit) for filter and CI options.

### Bootstrap or refresh command pages

```bash
php artisan modularous:generate:command:docs --force
```

Walks every registered `modularous:*` command and writes a boilerplate `.md` page per command. Use `--force` to overwrite existing files. See [generate-command-docs](./generate-command-docs).

### Enforce documentation in CI

```bash
php artisan modularous:docs:audit --fail-on-missing
```

Exits with code 1 when undocumented source files exist — wire this into a CI job to block merges that add undocumented classes.
