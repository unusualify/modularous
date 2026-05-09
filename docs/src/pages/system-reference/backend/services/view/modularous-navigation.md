---
sidebarPos: 4
sidebarTitle: ModularousNavigation
---

# ModularousNavigation

**File**: `src/Services/View/ModularousNavigation.php`

`ModularousNavigation` builds the sidebar navigation array that is passed to the frontend. It resolves route names, applies permission checks, resolves badge values, and marks the active item based on the current request URL.

## Navigation Types

Each sidebar config can contain entries keyed by user type:

| Type | Audience |
|------|----------|
| `default` | Authenticated users |
| `superadmin` | Super-admin users only |
| `client` | Client-role users |
| `guest` | Unauthenticated visitors |

## Key Methods

| Method | Description |
|--------|-------------|
| `systemMenu()` | Returns the navigation array built from Modularous's system modules |
| `modulesMenu()` | Returns the navigation array built from application modules |
| `sidebarMenuItem($array)` | Processes a single nav item: checks `can`/`allowedRoles`, resolves route, evaluates badge, filters invisible items |
| `formatSidebarMenus(&$array)` | Iterates all user types and calls `formatSidebarMenu` on each |
| `formatSidebarMenu($array)` | Recursively processes each item; removes items that fail permission checks |
| `setActiveSidebarItems(&$items)` | Traverses the tree and sets `is_active = 1` on items whose route matches the current URL |
| `unsetMenuKeys(&$array)` | Re-indexes arrays to remove string keys (ensures JSON encodes as array, not object) |
| `sidebarMenuFromModules($modules)` | Builds the full navigation tree from a collection of module instances |

## sidebarMenuItem Processing

For each nav item in the config array:

1. **Permission check** — if `can` is set, evaluates `$user->can($array['can'])`; if `allowedRoles` is set, calls `isAllowedItem()`; returns `false` (item removed) on failure.
2. **Nested items** — recursively processes `items` / `menuItems` arrays.
3. **Route resolution** — converts `route_name` to a full route URL via `Route::hasAdmin()`; returns `false` if the route does not exist.
4. **Badge resolution** — if `connector` is set, runs it and assigns the result; if `badge` is a `callable`, invokes it; badges `< 1` are removed to avoid showing zeros.
5. **Active detection** — sets `is_active = 1` if the item's route equals the current request URL.

## Badge Active Styling

When a nav item is both active and has a numeric badge, `ModularousNavigation` applies contrasting badge styling:

```php
$item['badgeProps'] = ['color' => 'white', 'class' => 'primary'];
$item['iconProps'] = [];
unset($item['class']);
```

## Module-Driven Navigation

`sidebarMenuFromModules()` discovers route names and headlines from module metadata, automatically building the nav tree from the modules that are enabled in the application. Sub-modules appear as nested items under their parent module.

## Example Config Shape

```php
// In a module's nav config
return [
    'default' => [
        [
            'name'       => 'Orders',
            'icon'       => 'mdi-cart',
            'route_name' => 'orders.index',
            'badge'      => 'Orders|index^Order->pendingCount',  // connector
            'can'        => 'view-orders',
        ],
    ],
];
```

After processing, the frontend receives:

```json
{
  "name": "Orders",
  "icon": "mdi-cart",
  "route": "https://app.example.com/admin/orders",
  "badge": 14,
  "is_active": 0
}
```
