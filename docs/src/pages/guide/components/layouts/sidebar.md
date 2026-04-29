---
sidebarPos: 2
sidebarTitle: Sidebar
---

# Sidebar (`ue-sidebar`)

`ue-sidebar` is the navigation drawer component. It reads sidebar state from `useSidebar()` and renders [`SidebarContent`](./sidebar-content) either directly or inside a **fully-hidden hover wrapper**, depending on the active display mode.

> [!NOTE]
> You do not use `ue-sidebar` directly. It is mounted by [`ue-main`](./main) via the `hideDefaultSidebar` prop.

## Fully-hidden mode

When `expandHover` is set to `'hidden'` in `ui_settings`, the sidebar is completely off-screen. A transparent **hover zone** (12 px wide) sits flush against the left edge of the viewport. On desktop (`lgAndUp`), hovering over this zone commits `CONFIG.SET_SIDEBAR = true` to the store, which causes the drawer to slide in.

```
┌──────────────────────────────────────────────┐
│░  (hover zone, 12px)                         │
│░  ← mouse enters here → sidebar opens        │
└──────────────────────────────────────────────┘
```

Moving the mouse outside the wrapper triggers `handleSidebarLeave`, which closes the drawer again.

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `items` | `Array` | required | Navigation items forwarded to `ue-navigation-group`. |
| `profileMenu` | `Array` | `[]` | Profile popover items shown in the sidebar footer. |
| `logoSymbol` | `String` | `'main-logo-dark'` | SVG symbol name for the sidebar logo. The mini symbol is resolved automatically via `getLocaleSymbol`. |
| `rating` | `Number` | `0` | Reserved; not currently used in the template. |

## Slots

| Slot | Description |
|---|---|
| `bottom` | Content appended at the very bottom of the drawer (below the user footer). Passed through to `SidebarDrawerContent`. Used by `ue-main` for `ue-navigation-group` bottom items and `ue-impersonate-toolbar`. |

## Exposed methods

| Method | Description |
|---|---|
| `profileFormSubmitted(res)` | Called by `ue-main` after a profile save; re-fetches the user profile from `URLS.profileShow`. |

## Sidebar state (from `useSidebar`)

`ue-sidebar` consumes the following reactive values from `useSidebar()`. These are not props — they are driven entirely by the store and `ui_settings`:

| Value | Description |
|---|---|
| `fullyHidden` | Enables the hover-zone wrapper |
| `hoverZoneWidth` | Width (px) of the transparent hover strip |
| `status` | Whether the drawer is open |
| `rail` | Whether the drawer is in icon-only rail mode |
| `isHoverable` | Enables Vuetify's built-in `expand-on-hover` |
| `hideIcons` | Hides nav icons (text-only mode) |
| `width` | Drawer width in px |
| `effectivePersistent / Permanent / Temporary` | Vuetify drawer behaviour props |
| `railManual` | Whether the user has manually collapsed to rail |

## Active menu

`ue-sidebar` provides `activeMenu` (from `useSidebar`) to all descendants via Vue's `provide/inject` API under the key `'activeMenu'`. Navigation group items use this to highlight the active route.

## Auto-scroll to active item

On `onMounted`, the component scrolls `.sidebar-item-active` into view within `.v-navigation-drawer__content` using `useGoTo` with 200 ms easing.
