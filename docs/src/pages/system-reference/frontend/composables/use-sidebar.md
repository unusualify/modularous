---
sidebarTitle: useSidebar
---

# useSidebar

Manages the full sidebar state: open/close, rail mode, expand-on-hover behaviour, drag-to-resize, and user preference persistence.

**File:** `vue/src/js/hooks/useSidebar.js`

---

## Usage

```js
import { useSidebar } from '@/hooks'

const {
  status,
  rail,
  width,
  options,
  expandHover,
  handleRailToggle,
  handleResizeStart,
  handleResizing,
  handleResizeEnd,
  handleSidebarLeave
} = useSidebar()
```

```html
<v-navigation-drawer
  v-model="status"
  :rail="rail"
  :width="width"
  :permanent="effectivePermanent"
  :temporary="effectiveTemporary"
  @mousedown.native="handleResizeStart"
>
  ...
</v-navigation-drawer>
```

## Returns

### State

| Name | Type | Description |
|------|------|-------------|
| `status` | `ComputedRef<Boolean>` | Sidebar open/closed (read/write — synced with Vuex `config.sidebarStatus`) |
| `rail` | `ComputedRef<Boolean>` | True when in rail (mini) mode on `lgAndUp` |
| `railManual` | `Ref<Boolean>` | Local rail toggle state (persisted to user prefs) |
| `width` | `ComputedRef<Number>` | Current sidebar width in px |
| `railWidth` | `ComputedRef<Number>` | Rail-mode width (default `56`) |
| `options` | `ComputedRef<Object>` | Merged config + user preferences for the sidebar |
| `expandHover` | `ComputedRef<String>` | `'mini'` \| `'hidden'` — expand-on-hover strategy |
| `fullyHidden` | `ComputedRef<Boolean>` | True when `expandHover === 'hidden'` |
| `sidebarLocation` | `ComputedRef<String>` | `'left'` or `'right'` |
| `hideIcons` | `ComputedRef<Boolean>` | True when not in rail and `options.hideIcons` is set |
| `isHoverable` | `ComputedRef<Boolean>` | True when expand-on-hover is active |
| `sidebarPinned` | `ComputedRef<Boolean>` | Whether the user has pinned the sidebar open |
| `effectivePersistent` | `ComputedRef<Boolean>` | Whether the sidebar participates in Vuetify layout (narrows main content) |
| `effectivePermanent` | `ComputedRef<Boolean>` | Always visible and layout-aware on desktop in mini mode |
| `effectiveTemporary` | `ComputedRef<Boolean>` | Overlay mode (does not affect main content width) |
| `isResizing` | `Ref<Boolean>` | True while the user is dragging the resize handle |
| `open` | `Array` | Open state for nested navigation groups |
| `activeMenu` | `ComputedRef<String>` | Active menu item anchor (e.g. `'#profile'`) |

### Methods

| Name | Signature | Description |
|------|-----------|-------------|
| `handleRailToggle` | `() => void` | Toggle rail mode and persist the preference |
| `handleResizeStart` | `(e) => void` | Begin drag resize (mousedown on the resize handle) |
| `handleResizing` | `(e) => void` | Update sidebar width during drag (mousemove) |
| `handleResizeEnd` | `() => void` | Finish resize and persist the new width |
| `handleSidebarLeave` | `() => void` | Close the sidebar when in hidden mode and not pinned |
| `handleMenu` | `(title) => void` | Set `activeMenu` to `#title` |
| `handleProfile` | `(event) => void` | Expand profile section on hover if `expandOnHover` is configured |

## Expand strategies

| `expandHover` | Behaviour |
|---------------|-----------|
| `'mini'` | Sidebar is always visible (rail or expanded). Toggling rail narrows/expands while the sidebar stays in the Vuetify layout. |
| `'hidden'` | Sidebar is an overlay. It appears on hover over the left edge or when opened programmatically. Pinning it makes it persistent. |

## Drag-to-resize

The resize handle triggers `handleResizeStart` (mousedown). Global `mousemove` / `mouseup` listeners (added in `onMounted`) call `handleResizing` and `handleResizeEnd`. The width is clamped to `[256, 400]` px and persisted via `useNavigationLayout.persistUiPreferences`.

## See Also

- [useNavigationLayout](/system-reference/frontend/composables/use-navigation-layout) — topbar and bottom-nav config merging; provides `persistUiPreferences`
