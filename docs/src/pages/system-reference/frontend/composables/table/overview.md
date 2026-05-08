---
sidebarPos: 1
sidebarTitle: Overview
---

# Table Sub-hooks

`useTable` is composed from 11 focused sub-hooks that each own a specific slice of the table's behavior. They live under `vue/src/js/hooks/table/` and are exported from `@/hooks/table`.

You rarely need to use these directly — `useTable` wires them together. Read these pages when you need to extend or replace a specific behavior.

| Sub-hook | File | Purpose |
|----------|------|---------|
| [useTableActions](/system-reference/frontend/composables/table/use-table-actions) | `useTableActions.js` | Toolbar / bulk action props |
| [useTableFilters](/system-reference/frontend/composables/table/use-table-filters) | `useTableFilters.js` | Search, status tabs, advanced filters |
| [useTableForms](/system-reference/frontend/composables/table/use-table-forms) | `useTableForms.js` | Create/edit form state |
| [useTableGroup](/system-reference/frontend/composables/table/use-table-group) | `useTableGroup.js` | Client-side column grouping |
| [useTableHeaders](/system-reference/frontend/composables/table/use-table-headers) | `useTableHeaders.js` | Column visibility and localStorage |
| [useTableItem](/system-reference/frontend/composables/table/use-table-item) | `useTableItem.js` | Edited item and soft-delete detection |
| [useTableItemActions](/system-reference/frontend/composables/table/use-table-item-actions) | `useTableItemActions.js` | Per-row action dispatch |
| [useTableIterator](/system-reference/frontend/composables/table/use-table-iterator) | `useTableIterator.js` | Iterator (card/list) layout actions |
| [useTableModals](/system-reference/frontend/composables/table/use-table-modals) | `useTableModals.js` | Delete / custom / show modals |
| [useTableNames](/system-reference/frontend/composables/table/use-table-names) | `useTableNames.js` | i18n titles and dialog text |
| [useTableState](/system-reference/frontend/composables/table/use-table-state) | `useTableState.js` | URL/localStorage state persistence |
