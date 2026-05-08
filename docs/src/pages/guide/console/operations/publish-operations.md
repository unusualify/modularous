---
sidebarPos: 3
sidebarTitle: Operations Publish
---

# Operations Publish

> Publish the Modularous operations vendor assets into the host application.

::: warning Hidden command
This command has `$hidden = true` and does not appear in `php artisan list`. It is intended for advanced / internal use.
:::

## Command Information

- **Signature:** `modularity:publish:operations`
- **Category:** Operations

## What It Does

Runs `php artisan vendor:publish --tag=operations` to copy the operations configuration and migration stubs from the Modularous package into the host application's vendor publish paths.

Run this once after installing or upgrading Modularous if your application uses the Operations pipeline.

## Example

```bash
php artisan modularity:publish:operations
```

## Related

- [modularity:operations:process](/guide/console/operations/process-operations) — process pending operations
