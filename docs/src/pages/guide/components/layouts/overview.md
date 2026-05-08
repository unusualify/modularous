---
sidebarPos: 38
sidebarTitle: Overview
sidebarGroupTitle: Layouts
---

# Layouts

Modularous provides a layered application shell built from six components. In practice you only configure [`ue-main`](./main) — the rest are assembled automatically.

## Component hierarchy

```
ue-main  (Main.vue)
├── v-app-bar          — optional top bar
├── ue-sidebar  (Sidebar.vue)
│   └── ue-sidebar-content  (SidebarContent.vue)
│       ├── ue-sidebar-drawer-content  (SidebarDrawerContent.vue)
│       │   ├── [prepend]  app name + logo + rail toggle
│       │   ├── ue-navigation-group  — nav items
│       │   └── [append]   user info, profile menu, logout, About
│       └── resize handle (drag to resize)
├── v-main             — page content slot
├── v-bottom-navigation — optional mobile nav bar
└── singleton modals
    ├── ue-modal-media      — media library
    ├── ue-modal (profile)  — profile image upload
    ├── ue-modal (alert)    — dialog-style alerts
    ├── ue-modal (login)    — session-expired re-login
    ├── ue-alert            — snackbar/toast alerts
    └── ue-dynamic-modal    — programmatically triggered modals
```

## Components

| Component | Tag | Role |
|---|---|---|
| [`Main`](./main) | `ue-main` | Top-level app shell — the only component you configure directly |
| [`Sidebar`](./sidebar) | `ue-sidebar` | Navigation drawer with hover-zone support for fully-hidden mode |
| [`SidebarContent`](./sidebar-content) | `ue-sidebar-content` | Adds resize handle and secondary drawer slots around the drawer |
| [`SidebarDrawerContent`](./sidebar-drawer-content) | `ue-sidebar-drawer-content` | The `v-navigation-drawer` — header, nav list, user footer |
| [`Home`](./home) | — | Legacy prototype layout *(not for production use)* |
| [`Footer`](./footer) | — | Legacy prototype footer *(not for production use)* |

## Sidebar display modes

The sidebar behaviour is driven by `ui_settings` in the Modularous config and the user's saved preferences. The four effective modes are:

| Mode | Description |
|---|---|
| **Persistent expanded** | Full-width drawer, always visible on desktop |
| **Rail** | Icon-only strip; expands on hover (`expandHover: 'mini'`) |
| **Fully hidden** | Completely off-screen; a hover zone on the left edge triggers it (`expandHover: 'hidden'`) |
| **Temporary** | Overlay drawer (default on mobile) |

You do not pass these modes as props — they are resolved from store state inside `useSidebar()`.
