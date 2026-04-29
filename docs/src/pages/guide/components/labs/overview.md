---
sidebarPos: 99
sidebarTitle: Overview
sidebarGroupTitle: Labs
---

# Labs <Badge type="warning" text="experimental" />

The **Labs** section contains components and input types that are experimental, in early development, or domain-specific. They are functional but may have incomplete APIs, limited test coverage, or may change without a semver notice.

## Input types

These components integrate with the `ue-form` schema system via `useInput`. Use their type name in a field schema to activate them.

| Component | Schema type | Description |
|---|---|---|
| [`InputDate`](./input-date) | `date` | Native HTML date picker with ISO normalisation |
| [`InputTime`](./input-time) | `time` | Time picker via a `v-time-picker` popover |
| [`InputRange`](./input-range) | `range` | Dual-handle range slider |
| [`InputColor`](./input-color) | `color` | Hex color picker with swatch preview |
| [`InputTreeview`](./input-treeview) | `treeview` | Hierarchical tree selection |
| [`InputIcon`](./input-icon) | `icon` | MDI icon picker with search |
| [`InputOtp`](./input-otp) | `otp` | One-time password input *(stub)* |

## Display / layout components

| Component | Description |
|---|---|
| [`Callout`](./callout) | Bordered alert card with a title and value |
| [`RowFormat`](./row-format) | Flexible labelled row layout using a column array |

## Specialised components

| Component | Description |
|---|---|
| [`ActiveTableItem`](./active-table-item) | Modal-driven detail panel for a selected table row |
| [`PressReleaseCardIterator`](./press-release-card-iterator) | Card iterator row for press release data tables |
