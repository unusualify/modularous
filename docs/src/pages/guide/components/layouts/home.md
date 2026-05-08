---
sidebarPos: 5
sidebarTitle: Home (legacy)
---

# Home <Badge type="danger" text="legacy" />

`Home` is a legacy prototype layout component. It renders a `v-app` containing only a `Sidebar` with five hardcoded icon items. It is **not used in production** and exists only as an early development artifact.

> [!WARNING]
> Do not use this component. Use [`ue-main`](./main) for all application layouts.

## What it does

```html
<v-app id="inspire" :style="{background: $vuetify.theme.themes.dark.background}">
  <Sidebar :items="[
    {icon: 'fas fa-plus'},
    {icon: 'fas fa-th-large'},
    {icon: 'fas fa-align-center'},
    {icon: 'fas fa-gitter'},
    {icon: 'fas fa-chart-line'},
  ]" />
</v-app>
```

It uses Vuetify 2 theme syntax (`$vuetify.theme.themes.dark.background`) which is no longer valid in Vuetify 3.
