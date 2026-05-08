---
sidebarPos: 1
sidebarTitle: Main
---

# Main (`ue-main`)

`ue-main` is the root application shell. It wraps `v-app` and composes the top bar, sidebar, page content area, optional bottom navigation, and a set of singleton modals that are available everywhere in the app.

## Usage

Pass the `navigation` object from your Inertia/backend page props:

```html
<ue-main
  :navigation="navigation"
  header-title="My App"
  :impersonation="impersonation"
  :authorization="authorization"
>
  <!-- page content -->
</ue-main>
```

The `navigation` prop is typically shared via Inertia's `HandleInertiaRequests` middleware and has this shape:

```php
'navigation' => [
    'sidebar'      => $this->buildSidebarItems(),
    'sidebarBottom' => [],
    'profileMenu'  => $this->buildProfileMenu(),
    'breadcrumbs'  => [],
]
```

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `navigation` | `Object` | `{ sidebar: [], breadcrumbs: [], profileMenu: [], sidebarBottom: [] }` | Navigation data. Keys: `sidebar`, `sidebarBottom`, `profileMenu`, `breadcrumbs`. |
| `headerTitle` | `String` | `''` | Text shown in the centre of the top app bar. |
| `hideDefaultSidebar` | `Boolean` | `false` | Omit the sidebar entirely (e.g. for login/auth pages). |
| `fixedAppBar` | `Boolean` | `false` | Force the top bar to render regardless of `ui_settings`. |
| `appBarOrder` | `Number` | `0` | Vuetify `order` for the `v-app-bar` when `fixedAppBar` is `true`. |
| `sidebarAttributes` | `Object` | `{}` | Extra props forwarded to `ue-sidebar`. |
| `impersonation` | `Object` | `{}` | Config for the impersonation toolbar. See [Impersonation](#impersonation). |
| `authorization` | `Object` | `{}` | Auth flags used to gate the media library. See [Authorization](#authorization). |

## Slots

| Slot | Description |
|---|---|
| `default` | Main page content. Rendered inside `v-main`. |
| `top` | Content rendered above the default slot inside `v-main`. |
| `bottom` | Content rendered below the default slot inside `v-main`. |
| `app-bar` | Replaces the entire default `v-app-bar` inner content. Receives no bindings. |
| `bottom-nav` | Replaces the default items inside `v-bottom-navigation` (mobile). |

## Navigation prop

| Key | Type | Description |
|---|---|---|
| `sidebar` | `Array` | Items passed to `ue-sidebar` / `ue-navigation-group`. |
| `sidebarBottom` | `Array` | Items pinned to the bottom of the sidebar via `ue-navigation-group`. |
| `profileMenu` | `Array` | Items for the collapsible profile menu in the sidebar footer. |
| `breadcrumbs` | `Array` | Reserved; not currently consumed by `Main.vue` directly. |

## Top bar

The top bar is a `v-app-bar` that renders when `showTopbar` is `true`. The value is derived from `useNavigationLayout()` (which reads `ui_settings.topbar`) unless `fixedAppBar` is `true`, which forces it on.

Default top-bar content:
- **Hamburger icon** — shown on screens below `lg`; toggles the sidebar via `$toggleSidebar()`.
- **Title** — centred, driven by `headerTitle` prop.
- **Avatar** — shows the current user's avatar; clicking opens the profile dialog.

Override the entire bar using `#app-bar`.

## Bottom navigation

A `v-bottom-navigation` is rendered when `showBottomNav` is `true` (resolved from `useNavigationLayout()`). Default items are a **Home** button and a **Profile** button. Override with `#bottom-nav`.

## Singleton modals

`ue-main` mounts the following modals once for the entire application:

| Modal | Trigger | Description |
|---|---|---|
| `ue-modal-media` | `store.state.mediaLibrary.showModal` | Full media library browser |
| Profile dialog | `store.state.user.profileDialog` / `$openProfileDialog` | Avatar upload form |
| Alert dialog | `store.state.alert.dialog` | Large-format dialog alert |
| Login modal | `store.state.user.showLoginModal` | Session-expired re-login form |
| `ue-alert` | `store.state.alert` | Snackbar/toast notifications |
| `ue-dynamic-modal` | programmatic API | Dynamically triggered modals |

## Impersonation

Pass an `impersonation` object to show the impersonation toolbar at the bottom of the sidebar:

```php
'impersonation' => [
    'active'        => auth()->user()->isImpersonating(),
    'fetchEndpoint' => route('impersonate.users'),
    'route'         => route('impersonate.start', ':id'),
    'stopRoute'     => route('impersonate.stop'),
]
```

| Key | Type | Description |
|---|---|---|
| `active` | `Boolean` | Shows the toolbar when `true` |
| `fetchEndpoint` | `String` | API endpoint to search users |
| `route` | `String` | URL template for starting impersonation (`:id` is replaced) |
| `stopRoute` | `String` | URL to stop impersonation |

## Authorization

The `authorization` object controls whether the media library modal is mounted:

```php
'authorization' => [
    'isClient' => $user->isClient(),
]
```

The media library is accessible when `authorization` is a non-empty object and `isClient` is `false`.

## CSS classes on `v-app`

`Main.vue` applies these classes to the root `v-app` element based on sidebar state:

| Class | Condition |
|---|---|
| `ue-sidebar-expanded` | Desktop, sidebar open and not rail-only |
| `ue-sidebar-rail-only` | Desktop, rail mode active and sidebar open |
| `ue-sidebar-fully-hidden` | `expandHover === 'hidden'` and sidebar not pinned |

CSS custom properties set on `v-app`:

| Property | Set when |
|---|---|
| `--ue-sidebar-width` | Sidebar is expanded; value is the configured drawer width in px |
| `--ue-sidebar-rail-width` | Sidebar is in rail-only mode; value is the rail width in px |

## Development mode banner

When `store.getters.isHot` is `true` (HMR active), a green **"Development Mode"** chip is shown in the top-right corner of the screen. Clicking it dismisses it.
