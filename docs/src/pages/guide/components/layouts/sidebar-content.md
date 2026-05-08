---
sidebarPos: 3
sidebarTitle: Sidebar Content
---

# SidebarContent (`ue-sidebar-content`)

`SidebarContent` is an internal wrapper that sits between [`Sidebar`](./sidebar) and [`SidebarDrawerContent`](./sidebar-drawer-content). Its responsibilities are:

1. **Rendering the drawer** — passes all sidebar state props down to `ue-sidebar-drawer-content`.
2. **Resize handle** — adds an 8 px drag strip between the drawer and the main content area so users can resize the sidebar width.
3. **Secondary / content drawers** — conditionally mounts additional `v-navigation-drawer` instances when `options.contentDrawer` or `secondaryOptions` are configured.

> [!NOTE]
> This is an internal component. You do not use it directly.

## Resize handle

The resize handle is only shown when all of these are true:

- Sidebar is not in rail mode (`!rail`)
- Sidebar is open (`status === true`)
- Not in temporary (overlay) mode (`!effectiveTemporary`)
- Screen is desktop size (`$vuetify.display.lgAndUp`)

The handle is 8 px wide and uses `cursor: col-resize`. It highlights in `rgba(primary, 0.4)` on hover or while dragging. Dragging emits `resize-start` with the `mousedown` event, which is handled by `useSidebar`.

The handle's `left` (or `right` in right-to-left layouts) position tracks the current drawer `width` in px:

```
left: {width}px   (ltr sidebar)
right: {width}px  (rtl sidebar, sidebarLocation === 'right')
```

## Secondary drawers

Two optional `v-navigation-drawer` instances are mounted when their configuration objects exist:

| Condition | Description |
|---|---|
| `options.contentDrawer?.exists` | A content-panel drawer, max-width 15%, location driven by `options.location` |
| `secondaryOptions?.exists` | A secondary navigation drawer at `secondaryOptions.location` |

Both are pass-through — their content is not managed by this component.

## Props

| Prop | Type | Required | Description |
|---|---|---|---|
| `items` | `Array` | Yes | Forwarded to `ue-sidebar-drawer-content` |
| `profileMenu` | `Array` | No | Forwarded to `ue-sidebar-drawer-content` |
| `miniSymbol` | `String\|Object` | No | Forwarded to `ue-sidebar-drawer-content` |
| `profileMenuOpen` | `Boolean` | No | Two-way bound with `ue-sidebar-drawer-content` |
| `status` | `Boolean` | Yes | Drawer open/closed state |
| `rail` | `Boolean` | Yes | Rail (icon-only) mode |
| `isHoverable` | `Boolean` | Yes | Vuetify expand-on-hover |
| `hideIcons` | `Boolean` | Yes | Text-only navigation mode |
| `options` | `Object` | Yes | Sidebar config object (location, railWidth, contentDrawer, etc.) |
| `width` | `Number\|String` | Yes | Drawer width in px |
| `effectivePersistent` | `Boolean` | Yes | Vuetify persistent prop |
| `effectivePermanent` | `Boolean` | Yes | Vuetify permanent prop |
| `effectiveTemporary` | `Boolean` | No | Vuetify temporary (overlay) prop |
| `railManual` | `Boolean` | Yes | Whether rail was set manually by the user |
| `secondaryOptions` | `Object` | No | Config for the secondary drawer |
| `isResizing` | `Boolean` | No | Adds `ue-sidebar-resize-active` class to the handle |
| `sidebarLocation` | `String` | No | `'left'` or `'right'`; controls handle position |

## Emits

| Event | Description |
|---|---|
| `update:status` | Forwarded from `ue-sidebar-drawer-content` |
| `update:profileMenuOpen` | Forwarded from `ue-sidebar-drawer-content` |
| `activate-menu` | Forwarded from `ue-sidebar-drawer-content` |
| `rail-toggle` | Forwarded from `ue-sidebar-drawer-content` |
| `resize-start` | Emitted on resize handle `mousedown` |

## Slots

| Slot | Description |
|---|---|
| `bottom` | Passed through to `ue-sidebar-drawer-content` |
