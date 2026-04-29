---
sidebarPos: 14
sidebarTitle: Navigation
---

# Navigation

**Facade**: `Unusualify\Modularity\Facades\Navigation`  
**Accessor**: `modularity.navigation`  
**Underlying**: `Unusualify\Modularity\Services\View\ModularityNavigation`

Builds and formats the navigation data (sidebar, profile menu, bottom navigation) shared with Inertia pages. See [ModularityNavigation](/system-reference/backend/services/view/modularity-navigation) for implementation details.

## Usage

```php
use Unusualify\Modularity\Facades\Navigation;

// Format a raw navigation config array for the sidebar
$sidebar = Navigation::formatSidebarMenu(config('modularity.navigation.sidebar.default'));
```

The `get_modularity_navigation_config()` helper in `sources.php` uses this facade internally to assemble the full navigation object shared with every Inertia page.

## Notes

- Navigation items are role-scoped: `default`, `superadmin`, `client`, and `guest` configs are looked up per authenticated user role.
- Items are resolved through `Navigation::formatSidebarMenu()` which handles route resolution, permission checks, and icon mapping.
