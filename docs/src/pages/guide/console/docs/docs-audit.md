---
sidebarPos: 2
sidebarTitle: Docs Audit
---

# Docs Audit

> Audit source files against documentation pages and report gaps.

## Command Information

- **Signature:** `modularous:docs:audit [--section=SECTION] [--fail-on-missing]`
- **Category:** Docs

## Options

| Option | Default | Description |
|--------|---------|-------------|
| `--section=` | _(all sections)_ | Filter to a specific section label (e.g. `Entities`, `Controllers`, `Hydrates`). Case-insensitive substring match. |
| `--fail-on-missing` | `false` | Exit with code 1 when any source file is undocumented — use this in CI. |

## What It Does

Walks a registry of source-to-docs mappings defined in `DocsAuditCommand::sections()`. For each section, it scans the source directory for `.php` files, kebab-cases the class name, and checks whether a matching `.md` file exists in the paired docs directory. The final output is a summary table plus a per-section list of missing pages and a coverage percentage.

`index.md` files in the docs directory are ignored, since those are section overviews rather than per-class pages.

### Tracked sections

The audit ships with a registry covering the backend reference docs: Entities, Entity Enums, Entity Scopes, Entity Traits, Controllers, Middleware, HTTP Requests, View Composers, Facades, Helpers, Providers, Events, Notifications, Generators, Hydrates, Core Services, Package Traits, Contracts, Exceptions, Transformers, Activators, Brokers, and Repository Traits.

To track a new section, add an entry to the `sections()` array in `src/Console/Docs/DocsAuditCommand.php`:

```php
[
    'label' => 'My Section',
    'source' => 'src/MyDir',
    'docs' => 'docs/src/pages/system-reference/backend/my-section',
    'recursive' => true,               // optional, default false
    'exclude_dirs' => ['Traits'],      // optional
],
```

## Examples

### Full audit

```bash
php artisan modularous:docs:audit
```

### Audit a single section

```bash
php artisan modularous:docs:audit --section=Entities
php artisan modularous:docs:audit --section=hydrates
```

### CI gate

```bash
php artisan modularous:docs:audit --fail-on-missing
```

Exits with a non-zero status when undocumented classes exist, failing the build.

## Output

```
 INFO  Modularous Documentation Audit

  Package root: /path/to/packages/modularous

+-----------------+--------------+------------+---------------+
| Section         | Source Files | Documented | Status        |
+-----------------+--------------+------------+---------------+
| Entities        | 12           | 12         | ✓ Complete    |
| Controllers     | 18           | 15         | ✗ 3 missing   |
...
+-----------------+--------------+------------+---------------+

 INFO  Coverage: 142/150 files (95%)

 WARN  Missing documentation:

  Controllers ───────── 3 file(s)
  • GlideController — src/Http/Controllers/GlideController.php
  ...
```

## Related

- [generate-command-docs](./generate-command-docs) — scaffold Markdown pages for Artisan commands
- [Backend Reference](/system-reference/backend/overview) — the docs tree this command audits
