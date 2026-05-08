---
sidebarPos: 5
sidebarTitle: Custom Auth Pages
---

# Custom Auth Pages

Modularous provides a flexible authentication system that you can fully customize without modifying package code. All auth pages (login, register, forgot password, etc.) are driven by configuration files.

## Overview

- **Package Auth (UeAuth)**: Minimal, slot-based default component. No banner or app-specific content.
- **Custom Auth (UeCustomAuth)**: Your app-specific design. Add banner text, redirect buttons, split layouts, and any custom props.
- **Attribute flow**: All attributes from config are passed to the auth component via `v-bind`. Custom components receive whatever you define.

## Quick Start

1. Create `modularity/auth_pages.php` in your app (or merge into `config/modularity.php`).
2. Add `attributes` for banner content, redirect buttons, etc.
3. Optionally use a custom auth component: publish `Auth.vue` and set `component_name` to `ue-custom-auth`.

## Documentation

- [Overview & Architecture](./overview) — Package vs custom auth, attribute flow
- [Configuration](./configuration) — auth_pages and auth_component config structure
- [Attributes & Custom Props](./attributes) — Passing custom attributes to auth components
- [Custom Auth Component](./custom-auth-component) — Creating and using a custom Auth.vue
- [Layout Presets](./layout-presets) — banner, minimal, and structural options
- [Page Definitions](./page-definitions) — Per-page overrides (formDraft, formSlotsPreset, etc.)
