---
sidebarPos: 22
sidebarTitle: UIPreferencesController
---

# UIPreferencesController

**File**: `src/Http/Controllers/UIPreferencesController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers`  
**Extends**: `Controller`  
**Traits**: `MakesResponses`

Persists per-user UI preferences (sidebar state, topbar visibility, bottom navigation) to the database. A whitelist ensures only known preference keys can be written.

## Methods

### `update(Request $request): JsonResponse`

Merges the submitted preferences into the current user's stored preferences and saves them.

Only keys defined in the whitelist (see below) are accepted. Unknown keys are silently discarded by `filterAllowedPreferences()`.

### `filterAllowedPreferences(array $data): array`

Filters the input array against the whitelist and returns only allowed key–value pairs.

## Allowed Preference Keys

| Scope | Allowed keys |
|-------|-------------|
| `sidebar` | `rail`, `location`, `width`, `expandOnHover`, `hideIcons`, `pinned`, `status` |
| `topbar` | `enabled`, `fixed`, `order`, `showOnMobile`, `showOnDesktop` |
| `bottomNavigation` | `enabled`, `showOnMobile`, `showOnDesktop` |

Preferences are stored on the user model and loaded into the frontend store on every page load.

## Example Request

```json
{
  "sidebar": {
    "rail": true,
    "pinned": false
  },
  "topbar": {
    "enabled": true
  }
}
```

Submitted keys not present in the whitelist are dropped before saving.
