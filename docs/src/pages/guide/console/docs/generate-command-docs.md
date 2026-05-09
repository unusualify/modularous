---
sidebarPos: 3
sidebarTitle: Generate Command Docs
---

# Generate Command Docs

> Auto-generate Markdown reference pages for all registered `modularous:*` and `mod:*` Artisan commands.

## Command Information

- **Signature:** `modularous:generate:command:docs [--output=] [--f|force]`
- **Category:** Generators

## Options

| Option | Default | Description |
|--------|---------|-------------|
| `--output=` | `docs/src/pages/advanced-guide/commands` (vendor path) | Directory where Markdown files are written |
| `--force` / `-f` | `false` | Overwrite existing files |

## What It Does

Iterates over every command registered with the Laravel Kernel, filters to those whose name starts with `modularous:` or `mod:`, then writes one `.md` file per command. Each file is generated from the command's signature, description, arguments, and options — the same data that populates the auto-generated boilerplate sections in this documentation.

This command is the tool used to bootstrap the initial command docs. Manually curated pages should be written or edited afterward.

## Examples

```bash
# Generate to the default output path
php artisan modularous:generate:command:docs

# Generate to a custom directory
php artisan modularous:generate:command:docs --output=docs/commands

# Regenerate and overwrite existing files
php artisan modularous:generate:command:docs --force
```

## Related

- [Commands Overview](/guide/console/overview) — full command reference
