---
sidebarPos: 5
sidebarTitle: Datatable
---

# Datatable

`Datatable` wraps Vuetify's `v-data-table-server` with a standard toolbar (title, search, status filter, create button, delete confirmation modal) and flexible row actions. It is built on top of `useTable` and `makeTableProps`.

> [!NOTE]
> This component is an earlier-generation data table. For new work, consider `ue-table` which uses the same hook but has a more complete feature set. `Datatable` is retained for existing usages.

## Usage

```html
<datatable
  name="User"
  :columns="columns"
  :items="items"
  :items-length="total"
  :row-actions="rowActions"
  create-url="/users/create"
/>
```

## Props

Props are defined by `makeTableProps()` from `@/hooks/useTable`. Key props:

| Prop | Type | Description |
|---|---|---|
| `name` | `String` | Resource name used for translated labels (add button, list title) |
| `columns` | `Array` | Column definitions. Each entry: `{ key, title, formatter? }` |
| `items` | `Array` | Current page of row data |
| `itemsLength` | `Number` | Total number of items (for server-side pagination) |
| `rowActions` | `Array` | Action buttons per row. Each: `{ name, icon, color, label? }` |
| `rowActionsType` | `String` | `'dropdown'` renders a `v-menu`; any other value renders icon buttons |
| `createOnModal` | `Boolean` | Shows a create form inside a modal instead of navigating to `createUrl` |
| `editOnModal` | `Boolean` | Opens edit form in a modal |
| `noForm` | `Boolean` | Hides the create/edit form dialog entirely |
| `createUrl` | `String` | URL for the create link button (used when `createOnModal` is `false`) |
| `titleKey` | `String` | Item property used as the row display title |

## Toolbar

The toolbar is rendered inside `v-slot:top` of `v-data-table-server`:

| Element | Description |
|---|---|
| Table title | Displays `tableTitle` (from `useTable`) via `ue-title`. Override with `#header`. |
| Search | `v-text-field` bound to `search`; appends `mdi-magnify` icon |
| Status filter | Dropdown showing `mainFilters` (count per status). Calls `filterStatus(slug)`. |
| Create button | `v-btn-success` linking to `createUrl` or opening the create modal |
| Delete modal | `ue-modal` with a confirmation question and cancel/confirm buttons |

## Column formatters

Set `formatter` on a column definition to apply special rendering:

| `formatter` value | Behaviour |
|---|---|
| `'edit'` | Renders the cell value as a clickable `v-btn` that calls `editItem` |
| `'activate'` | Renders the cell value as a clickable `v-btn` that calls `activateItem` |
| `'switch'` | Renders a `v-switch` (true-value `1`, false-value `0`) that calls `itemAction(item, 'switch', value, key)` |
| Any other string | Passed to `handleFormatter(formatter, value)` which renders via `ue-recursive-stuff` |

## Row actions

Each action in `rowActions` is rendered per row:

| Key | Description |
|---|---|
| `name` | Action identifier passed to `itemAction(item, action)` |
| `icon` | MDI icon string, or falls back to `$name` Vuetify alias |
| `color` | Icon / button colour |
| `label` | Tooltip text (falls back to `name`) |

On small screens (`isSmAndDown`) or when `rowActionsType === 'dropdown'`, actions are grouped into a `v-menu` (activated by `$list` icon). Otherwise individual icon buttons with tooltips are rendered.

## Slots

| Slot | Binding | Description |
|---|---|---|
| `header` | `{ tableTitle }` | Replaces the toolbar title area |
| `formDialog` | — | Replaces the built-in create/edit modal (only when `createOnModal || editOnModal`) |

## Emits

Row actions trigger `itemAction(item, action)` from `useTable`, which typically dispatches a Vuex action or calls an API endpoint based on the action name.

## Table options

Pagination, sorting, and multi-sort are managed via `v-model:options` bound to `options` from `useTable`. The table is fixed-header, fixed-footer, and sticky with height `window.y - 64 - 24 - 59 - 36` to fill the viewport.
