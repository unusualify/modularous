---
sidebarPos: 2
sidebarTitle: ActiveNavigation
---

# ActiveNavigation

**Class**: `Unusualify\Modularous\Http\ViewComposers\ActiveNavigation`  
**Source**: `src/Http/ViewComposers/ActiveNavigation.php`

Parses the current route name into up to three navigation depth markers and injects them into every view. The frontend uses these variables to highlight the correct sidebar menu items.

## Injected Variables

| Variable | Type | Description |
|----------|------|-------------|
| `_global_active_navigation` | `string` | Second segment of the route name (index `[1]`) — the top-level module or section (e.g. `posts`) |
| `_primary_active_navigation` | `string\|null` | Third segment (index `[2]`) — the sub-section or action (e.g. `index`). Falls back to the first route parameter when the route has fewer than three segments. |
| `_secondary_active_navigation` | `string\|null` | Fourth segment (index `[3]`) — a nested sub-section. Collapses back to the primary segment when the fourth segment is `index`. |

## Route Name Convention

All admin routes follow the pattern `admin.{module}.{action}.{sub}`. The composer splits on `.` and reads each depth:

```
Route name: admin.posts.index
  _global_active_navigation   = 'posts'
  _primary_active_navigation  = 'index'
  _secondary_active_navigation = (not set)

Route name: admin.posts.categories.edit
  _global_active_navigation    = 'posts'
  _primary_active_navigation   = 'categories'
  _secondary_active_navigation = 'edit'

Route name: admin.posts.categories.index
  _global_active_navigation    = 'posts'
  _primary_active_navigation   = 'categories'
  _secondary_active_navigation = 'categories'  ← collapses 'index' to parent
```

## Fallback for Route Parameters

When the route has only two segments but has route parameters (e.g. `admin.posts` with `{id}`), `_primary_active_navigation` is set to the value of the first route parameter. This handles resource show/edit routes that omit the action segment.

## Notes

- Does nothing when `$request->route()` returns `null` (console, queue, or test context without a bound route).
- The composer uses `Arr::only()` to merge with any values already present on the view, so manually passed navigation overrides take precedence.
