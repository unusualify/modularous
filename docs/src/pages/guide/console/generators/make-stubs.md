---
sidebarPos: 26
sidebarTitle: Make Stubs
---

# Make Stubs

> Generate or regenerate the stub files (views, JS, config) for a module route.

## Command Information

- **Signature:** `modularity:make:stubs {module} {route} [--only=] [--except=] [--force] [--fix]`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | Yes | The module name (e.g. `Blog`) |
| `route` | Yes | The route name within the module (e.g. `posts`) |

## Options

| Option | Description |
|--------|-------------|
| `--only=` | Comma-separated list of stub types to generate (e.g. `view,js`) |
| `--except=` | Comma-separated list of stub types to skip |
| `--force` | Overwrite files that already exist |
| `--fix` | Fix model config errors instead of generating fresh files |

## What It Does

Delegates to `StubsGenerator`, which writes the Blade views, Vue page components, and supporting config files for the specified module/route combination. Use `--only` or `--except` to regenerate a subset of stubs after manual edits to avoid overwriting customised files.

## Examples

```bash
# Generate all stubs for Blog/posts
php artisan modularity:make:stubs Blog posts

# Regenerate only the view stubs, overwriting existing
php artisan modularity:make:stubs Blog posts --only=view --force

# Regenerate everything except the JS stubs
php artisan modularity:make:stubs Blog posts --except=js

# Fix config errors without regenerating
php artisan modularity:make:stubs Blog posts --fix
```

## Related

- [make:module](./make-module) — full module scaffold (calls this internally)
- [make:route](./make-route) — add a route entry
