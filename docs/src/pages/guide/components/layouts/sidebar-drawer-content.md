---
sidebarPos: 4
sidebarTitle: Sidebar Drawer Content
---

# SidebarDrawerContent (`ue-sidebar-drawer-content`)

`SidebarDrawerContent` is the innermost layout component — the actual `v-navigation-drawer`. It renders the logo/app header, the navigation list, and the user footer with profile menu, logout, and About dialog.

> [!NOTE]
> This is an internal component. You do not use it directly.

## Layout

```
┌─────────────────────────────────────┐
│  [prepend]                          │
│  ┌────────────────────────────────┐ │
│  │ mini-logo  App Name            │ │ ← app info header
│  │            app@email.com   ◀◀  │ │ ← rail toggle (desktop only)
│  └────────────────────────────────┘ │
│  ─────────────────────────────────  │
│                                     │
│  ue-navigation-group (items)        │ ← main nav list
│                                     │
│  [append]                           │
│  ─────────────────────────────────  │
│  ┌────────────────────────────────┐ │
│  │ avatar  User Name          ▼   │ │ ← user info + profile toggle
│  │         user@email.com         │ │
│  └────────────────────────────────┘ │
│  [ue-navigation-group profileMenu]  │ ← collapsible profile menu
│  Logout                             │
│  About                              │
│  [bottom slot]                      │ ← impersonation toolbar, etc.
└─────────────────────────────────────┘
```

## Prepend — app header

- Displays the app name and email from `store.getters.appName` / `store.getters.appEmail`.
- The avatar shows the `miniSymbol` SVG icon.
- A **rail toggle** button (`mdi-chevron-double-left` / `mdi-chevron-double-right`) is shown on desktop when `lgAndUp` is true. Clicking emits `rail-toggle`.
  - When `railManual` is `true` (drawer is manually collapsed) the icon points right; otherwise left.

## Body — navigation

Renders `ue-navigation-group` with:
- `items` — the full nav tree
- `hideIcons` — switches to text-only mode
- `showTooltip` — enabled when in rail mode without hover expand (`rail && !isHoverable`), so hovering a collapsed item shows its label in a tooltip

## Append — user footer

Visible only when the user is authenticated (`!store.getters.isGuest`):

| Element | Description |
|---|---|
| Avatar | Shows `userProfile.avatar_url`; clicking calls `$openProfileDialog` |
| Name / email | From `userProfile.name` and `userProfile.email`; email has a tooltip |
| Profile toggle | `mdi-chevron-down/up` button; emits `update:profileMenuOpen` |
| Profile menu | `ue-navigation-group` with `profileMenu` items; shown in a `v-expand-transition` |
| Logout | `ue-logout-modal` with CSRF token; shows tooltip when in collapsed rail mode |
| About | `v-dialog` showing package versions, app name, env, and debug state (superadmin only) |

The `bottom` slot is rendered after the user section (used for impersonation toolbar etc.).

## Props

| Prop | Type | Required | Default | Description |
|---|---|---|---|---|
| `items` | `Array` | Yes | — | Main navigation items |
| `profileMenu` | `Array` | No | `[]` | Profile popover navigation items |
| `miniSymbol` | `String` | No | `'main-logo-dark'` | SVG symbol for the prepend avatar |
| `profileMenuOpen` | `Boolean` | No | `false` | Controls the profile menu expand state |
| `status` | `Boolean` | Yes | — | Drawer open/closed (`v-navigation-drawer` `model-value`) |
| `rail` | `Boolean` | Yes | — | Icon-only rail mode |
| `isHoverable` | `Boolean` | Yes | — | `expand-on-hover` on the drawer |
| `hideIcons` | `Boolean` | Yes | — | Passed to `ue-navigation-group` |
| `options` | `Object` | Yes | — | Contains `location`, `railWidth`, etc. |
| `width` | `Number\|String` | Yes | — | Drawer width |
| `effectivePersistent` | `Boolean` | Yes | — | Vuetify `persistent` |
| `effectivePermanent` | `Boolean` | Yes | — | Vuetify `permanent` |
| `effectiveTemporary` | `Boolean` | No | `false` | Vuetify `temporary` |
| `railManual` | `Boolean` | Yes | — | Whether the user manually collapsed to rail |

## Emits

| Event | Payload | Description |
|---|---|---|
| `update:status` | `Boolean` | Drawer open/close state change |
| `update:profileMenuOpen` | `Boolean` | Profile menu expand toggle |
| `activateMenu` | event | Forwarded from `ue-navigation-group` profile menu |
| `rail-toggle` | — | Rail toggle button clicked |

## Slots

| Slot | Description |
|---|---|
| `bottom` | Appended after the user footer section in `[append]` |

## About dialog

The **About** entry is only visible when `store.getters.versions` is populated and the user is not a guest or a client. For superadmins it additionally shows `appName`, `appEnv`, and `appDebug` with colour-coded chips.
