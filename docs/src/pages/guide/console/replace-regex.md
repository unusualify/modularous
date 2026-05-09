---
sidebarPos: 18
sidebarTitle: Replace Regex
---

# Replace Regex

> Recursively apply a regex find-and-replace across all files in a directory.

::: warning Hidden command
This command has `$hidden = true` and does not appear in `php artisan list`.
:::

## Command Information

- **Signature:** `modularous:replace:regex {path} {pattern} {data} [--d|directory=] [--p|pretend]`
- **Alias:** `mod:replace:regex`
- **Category:** Module

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `path` | Yes | Root directory to walk (absolute or relative to `base_path()`) |
| `pattern` | Yes | PCRE regex pattern to search for (without delimiters — `/` is added automatically) |
| `data` | Yes | Replacement string (supports `$1` back-references) |

## Options

| Option | Default | Description |
|--------|---------|-------------|
| `--directory=` / `-d` | `**/*.php` | Glob pattern to filter which files are processed |
| `--pretend` / `-p` | `false` | Preview matched files and diffs without writing any changes |

## What It Does

Delegates to `RegexReplacement::run()`. Files inside `vendor/` or `node_modules/` are skipped unless `path` itself points inside those directories. An invalid regex or non-existent path causes an early error exit.

## Examples

```bash
# Preview matches without writing
php artisan modularous:replace:regex app/Modules "OldNamespace" "NewNamespace" --pretend

# Replace across all PHP files
php artisan modularous:replace:regex app/Modules "OldNamespace" "NewNamespace"

# Restrict to a specific glob pattern
php artisan modularous:replace:regex app "Foo\\\\Bar" "Baz\\\\Qux" --directory="**/*Controller.php"
```

## Related

- [RegexReplacement](/system-reference/backend/support/regex-replacement) — the underlying support class
