---
sidebarPos: 12
sidebarTitle: Overview
sidebarGroupTitle: Operations
---

# Operations Commands

Operations commands manage the Modularous **Operations** pipeline — a queued job system for processing module operations (long-running tasks generated via `modularity:make:operation`).

::: warning Internal commands
Both commands have `$hidden = true` and are not listed in `php artisan list`. They are intended for advanced use and internal tooling — in normal development you trigger operations from domain code, not the CLI.
:::

| Command | Description |
|---------|-------------|
| [modularity:operations:process](/guide/console/operations/process-operations) | Dispatch or run pending module operations |
| [modularity:publish:operations](/guide/console/operations/publish-operations) | Publish the operations vendor assets |

## When You Might Need These

Normal flow: code dispatches an operation → Laravel queues it → a queue worker picks it up. These commands are escape hatches for when that flow is interrupted.

| Situation | Command | Notes |
|-----------|---------|-------|
| Queue worker is down and pending operations are piling up | `modularity:operations:process` | One-shot run outside the queue |
| Testing / reproducing a stuck operation locally | `modularity:operations:process` | Run synchronously to see exceptions |
| Installing Modularous from source and assets haven't been published yet | `modularity:publish:operations` | Usually covered by `modularity:install` |

## Related

- [`modularity:make:operation`](../generators/make-operation) — scaffold a new operation class
- [Queue workers](https://laravel.com/docs/queues) — the default pipeline
