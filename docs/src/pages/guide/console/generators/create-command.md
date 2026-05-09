---
sidebarPos: 2
---

# Create Command

Create a new console command. Lives in `Console/Make/` (class: `MakeConsoleCommand`).

## Signature

```
modularous:make:command {name} {signature} {--d|description=}
```

**Aliases:** `mod:c:cmd`, `modularous:create:command` (deprecated)

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | Yes | Command name (e.g. `MyAction`) |
| `signature` | Yes | Full signature (e.g. `my:action {arg}`) |

## Options

| Option | Description |
|--------|-------------|
| `--description`, `-d` | Command description |

## Examples

```bash
php artisan modularous:make:command MyAction "my:action {arg}"
php artisan modularous:make:command CacheWarm "cache:warm" -d "Warm the cache"
```

## Output

Creates `src/Console/{StudlyName}Command.php` in the package root. The generated command extends `BaseCommand` and is placed in `Console/` (root), not in a subfolder.

## Folder Reference

| Command type | Folder | Class pattern |
|--------------|--------|---------------|
| Scaffolding | `Console/Make/` | `Make*Command` |
| Root commands | `Console/` | `*Command` |

See [Console Conventions](/system-reference/console-conventions) for full folder structure.
