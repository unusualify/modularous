---
sidebarPos: 3
sidebarTitle: Remove Module
---

# Remove Module

> Completely remove a module — roll back its migrations and delete its files.

## Command Information

- **Signature:** `modularous:remove:module {module}`
- **Aliases:** `mod:r:module`, `unusual:remove:module`, `m:r:m`
- **Category:** Module

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | Yes | The module name to remove (e.g. `Blog`) |

## What It Does

1. Disables the Modularous cache.
2. Rolls back all of the module's migrations (`modularous:migrate:rollback`).
3. Calls `Modularous::deleteModule()` to remove the module directory and unregister it.

::: danger Destructive
This permanently deletes the module directory and its database tables. There is no undo.
:::

## Example

```bash
php artisan modularous:remove:module Blog
```

## Related

- [route:disable](./route-disable) — disable a single route without removing the module
