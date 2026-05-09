---
sidebarPos: 2
sidebarTitle: Operations Process
---

# Operations Process

> Dispatch or execute pending module operations — synchronously, asynchronously, or via a named queue.

::: warning Hidden command
This command has `$hidden = true` and does not appear in `php artisan list`. It is intended for advanced / internal use.
:::

## Command Information

- **Signature:** `modularous:operations:process [--s|sync] [--a|async] [--queue=] [--t|test] [--i|isolated] [--l|local]`
- **Alias:** `mod:operations:process`
- **Category:** Operations

## Options

| Option | Default | Description |
|--------|---------|-------------|
| `--sync` / `-s` | `false` | Run operations synchronously in the current process |
| `--async` / `-a` | `false` | Dispatch operations as queued jobs |
| `--queue` | — | Queue name to dispatch jobs onto |
| `--test` / `-t` | `false` | Dry-run — resolve operations but do not execute or dispatch |
| `--isolated` / `-i` | `false` | Prevent concurrent runs (uses mutex / atomic lock) |
| `--local` / `-l` | `false` | Only process operations defined in the local application |

## What It Does

Delegates to the underlying `operations:process` Artisan command with the options forwarded. When `--sync` is set operations run immediately in the current process. When `--async` is set each operation is pushed onto the queue (using `--queue` if supplied). Without either flag the command uses the default configured dispatch mode.

## Examples

```bash
# Run all pending operations synchronously
php artisan modularous:operations:process --sync

# Dispatch operations onto the "low" queue
php artisan modularous:operations:process --async --queue=low

# Dry-run to see what would be processed
php artisan modularous:operations:process --test
```

## Related

- [modularous:publish:operations](/guide/console/operations/publish-operations) — publish vendor assets
