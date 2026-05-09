---
sidebarPos: 2
sidebarTitle: ModularousActivator
---

# ModularousActivator

**File**: `src/Activators/ModularousActivator.php`  
**Namespace**: `Unusualify\Modularous\Activators`  
**Implements**: `Nwidart\Modules\Contracts\ActivatorInterface`

`ModularousActivator` is the module-level status manager. It reads/writes module activation flags (by module name), persists them to a statuses file, and caches the resolved map.

## Configuration Keys

Read from `modules.activators.modularous.*`:

- `statuses-file` -> JSON file path for module statuses
- `cache-key` -> cache key for statuses map
- `cache-lifetime` -> cache lifetime for statuses map

Cache driver comes from `modules.cache.driver`; cache enable toggle is `modules.cache.enabled`.

## Core Responsibilities

| Method | Purpose |
|--------|---------|
| `enable(Module $module)` / `disable(Module $module)` | Mark module active/inactive by module name |
| `setActiveByName(string $name, bool $status)` | Persist one module status |
| `hasStatus(Module $module, bool $status)` | Check module status with default-false semantics |
| `getModulesStatuses()` | Resolve statuses from cache or JSON |
| `reset()` | Clear statuses file and cache |
| `delete(Module $module)` | Remove explicit status record for a module |

## Storage Format

The statuses file stores an object keyed by module name:

```json
{
  "Blog": true,
  "Shop": false
}
```

## Notes

- If `modules.cache.enabled` is `false`, reads are done directly from JSON file each time.
- Cache is always flushed after writes (`setActiveByName`, `delete`, `reset`).

## Related

- [ModuleActivator](./module-activator) — route-level status manager
- [Module System](/system-reference/modules)
