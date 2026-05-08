---
sidebarPos: 1
sidebarTitle: Overview
outline: deep
---

# Vue Hooks

Modularous ships 38 composable hooks under `vue/src/js/hooks/`. They are the primary building blocks for form inputs, table behaviour, UI state, and media management.

All hooks are exported from `@/hooks/` (the alias for `vue/src/js/hooks/index.js`).

```js
import { useAlert, useAuthorization, useModal } from '@/hooks'
```

## Getting Started

Every hook in Modularous follows the same high-level shape:

```js
const { state, methods } = useXxx(props, emit?)
```

- **Inputs**: either component `props` (reactive), a plain options object, or `(props, emit)` for hooks that forward `v-model` updates.
- **Output**: a plain object you destructure into reactive state (`ref` / `computed`) and methods. Never `reactive()`-wrapped — destructure freely without losing reactivity.
- **Lifecycle**: hooks that touch the DOM, the store, or globals (modals, alerts, media library) are safe to call inside `setup()`; they internally handle `onMounted` / `onUnmounted`.

### Minimal component

```vue
<script setup>
import { useModal, useAlert } from '@/hooks'

const { isOpen, open, close } = useModal()
const { success, error } = useAlert()

async function onSubmit() {
  try {
    await saveRecord()
    success('Saved')
    close()
  } catch (e) {
    error(e.message)
  }
}
</script>

<template>
  <v-btn @click="open">Edit</v-btn>
  <v-dialog v-model="isOpen">...</v-dialog>
</template>
```

## Composition Patterns

Hooks are small on purpose so they can be layered. The most common compositions follow.

### Forms: `useForm` + `useInput` + `useValidation`

`useForm` owns submit, validation, and schema/model sync. Individual inputs use `useInput` to read their schema slice, and `useValidation` to bind rules.

```js
// Form.vue (orchestrator)
const { formData, submit, isSubmitting, errors } = useForm(props, emit)

// Inside any input component
const { modelValue, boundProps } = useInput(props, emit)
const { rules } = useValidation(props.schema)
```

See [useForm](./use-form), [useInput](./use-input), [useValidation](./use-validation).

### Inputs that fetch: `useInput` + `useInputFetch`

Select / autocomplete inputs with remote data combine both:

```js
const { modelValue, boundProps } = useInput(props, emit)
const { items, loading, search } = useInputFetch(props.schema)
```

### Tables: `useTable` as a super-composable

`useTable` already wires the 11 sub-hooks. You rarely compose sub-hooks yourself — read them to **extend** a behaviour, not to rebuild it.

```js
// Table.vue
const table = useTable(props)
// table.headers, table.filters, table.items, table.editItem, ...
```

Only reach for `useTableHeaders`, `useTableFilters`, etc. when building a custom table UI that reuses part of the behaviour.

### Media uploads: input + hook pair

| Input component | Hook |
|-----------------|------|
| `VInputImage` | `useImage` — Media library selection |
| `VInputFile` | `useFile` — Media library selection |
| `VInputFilepond` | `useFilepond` — direct FilePond upload |

```js
const { open, selected } = useMediaLibrary({ type: 'image' })
const { modelValue, processFile } = useFilepond(props, emit)
```

### Global UI services

`useAlert`, `useDynamicModal`, `useSidebar` are **singletons** backed by Vuex / provide-inject. Call them from anywhere without wiring:

```js
const { info, success, error } = useAlert()
const { open: openModal } = useDynamicModal()
```

### App state

`useConfig`, `useUser`, `useLocale`, `useAuthorization`, `useCache` read from the Vuex store. They are the idiomatic way to access global state — avoid reading `window.__*` or `store.state.*` directly.

```js
const { user, can } = useUser()
const { t, locale } = useLocale()
const { config } = useConfig()

if (can('edit', 'posts')) { /* ... */ }
```

## Conventions

### Naming

| Pattern | Meaning |
|---------|---------|
| `useXxx` | Main composable (imported from `@/hooks`) |
| `makeXxxProps` | Vuetify `propsFactory` export — reuse props on a component |
| `useXxx/useYyy` | Sub-hook (in subfolder, e.g. `hooks/table/useTableHeaders`) |

### Props factories

Most hooks that consume props also export a `makeXxxProps` factory. Use it when building a component that should accept the same props:

```js
import { makeModalProps } from '@/hooks/useModal'

export default defineComponent({
  props: {
    ...makeModalProps(),
    title: String,
  },
  setup(props, ctx) {
    const modal = useModal(props)
    // ...
  },
})
```

### Return shape

Hooks return plain objects. Do **not** wrap them in `reactive()` at the call site — individual `ref` / `computed` values stay reactive when destructured.

```js
// Correct
const { isOpen, open, close } = useModal()

// Wrong — loses reactivity on destructure
const modal = reactive(useModal())
```

### When a hook isn't the answer

- For **one-off local state**, plain `ref` is fine; don't invent a hook for two lines.
- For **pure utilities** (formatters, validators), put them in `vue/src/js/utils/` — hooks are for stateful or reactive behaviour.

## Full Hook Reference

| Hook | File | Purpose |
|------|------|---------|
| [useActiveTableItem](/system-reference/frontend/composables/use-active-table-item) | `useActiveTableItem.js` | Active row / detail-panel state in tables |
| [useAlert](/system-reference/frontend/composables/use-alert) | `useAlert.js` | Trigger global alert notifications |
| [useAuthorization](/system-reference/frontend/composables/use-authorization) | `useAuthorization.js` | Permission and role checks in Vue |
| [useCache](/system-reference/frontend/composables/use-cache) | `useCache.js` | Client-side key-value cache via Vuex |
| [useCastAttributes](/system-reference/frontend/composables/use-cast-attributes) | `useCastAttributes.js` | Dynamic `$notation` attribute interpolation |
| [useCurrency](/system-reference/frontend/composables/use-currency) | `useCurrency.js` | Currency value helpers |
| [useCurrencyNumber](/system-reference/frontend/composables/use-currency-number) | `useCurrencyNumber.js` | Number formatting with currency |
| [useConfig](/system-reference/frontend/composables/use-config) | `useConfig.js` | Access app config from Vuex |
| [useDraggable](/system-reference/frontend/composables/use-draggable) | `useDraggable.js` | Drag-and-drop (Sortable.js) props and state |
| [useDynamicModal](/system-reference/frontend/composables/use-dynamic-modal) | `useDynamicModal.js` | Inject-based global modal service |
| [useFile](/system-reference/frontend/composables/use-file) | `useFile.js` | File media-library input state |
| [useFilepond](/system-reference/frontend/composables/use-filepond) | `useFilepond.js` | FilePond upload props and validation rules |
| [useForm](/system-reference/frontend/composables/use-form) | `useForm.js` | Top-level form state, submit, validation |
| [useFormBase](/system-reference/frontend/composables/use-form-base) | `useFormBase.js` | FormBase flattening and field iteration |
| [useFormBaseLogic](/system-reference/frontend/composables/use-form-base-logic) | `useFormBaseLogic.js` | FormBase rendering logic |
| [useFormatter](/system-reference/frontend/composables/use-formatter) | `useFormatter.js` | Table column value formatters |
| [useImage](/system-reference/frontend/composables/use-image) | `useImage.js` | Image media-library input state |
| [useInertiaRequests](/system-reference/frontend/composables/use-inertia-requests) | `useInertiaRequests.js` | Inertia.js in-flight request state |
| [useInput](/system-reference/frontend/composables/use-input) | `useInput.js` | Base input state, `modelValue`, schema binding |
| [useInputFetch](/system-reference/frontend/composables/use-input-fetch) | `useInputFetch.js` | Paginated remote data fetch for select inputs |
| [useInputHandlers](/system-reference/frontend/composables/use-input-handlers) | `useInputHandlers.js` | Slot-driven input click handlers |
| [useItemActions](/system-reference/frontend/composables/use-item-actions) | `useItemActions.js` | Form action buttons (request / modal / download / blank) |
| [useLocale](/system-reference/frontend/composables/use-locale) | `useLocale.js` | Active locale helpers |
| [useMediaItems](/system-reference/frontend/composables/use-media-items) | `useMediaItems.js` | Selected media item list management |
| [useMediaLibrary](/system-reference/frontend/composables/use-media-library) | `useMediaLibrary.js` | Open/close media library modal |
| [useModal](/system-reference/frontend/composables/use-modal) | `useModal.js` | Modal open/close, width, fullscreen state |
| [useModelValue](/system-reference/frontend/composables/use-model-value) | `useModelValue.js` | `v-model` two-way binding helper |
| [useModule](/system-reference/frontend/composables/use-module) | `useModule.js` | Module name translation and metadata |
| [useNavigationLayout](/system-reference/frontend/composables/use-navigation-layout) | `useNavigationLayout.js` | Topbar / bottom-nav config merging |
| [useRandKey](/system-reference/frontend/composables/use-rand-key) | `useRandKey.js` | Unique component instance key |
| [useRepeater](/system-reference/frontend/composables/use-repeater) | `useRepeater.js` | Repeater block state, add / delete / duplicate |
| [useRoot](/system-reference/frontend/composables/use-root) | `useRoot.js` | Vuetify / root instance access |
| [useSidebar](/system-reference/frontend/composables/use-sidebar) | `useSidebar.js` | Sidebar open/close, rail, resize |
| [useSvg](/system-reference/frontend/composables/use-svg) | `useSvg.js` | SVG symbol existence and locale lookup |
| [useTable](/system-reference/frontend/composables/use-table) | `useTable.js` | Main data-table composable |
| [useUser](/system-reference/frontend/composables/use-user) | `useUser.js` | Authenticated user state and authorization proxy |
| [useValidation](/system-reference/frontend/composables/use-validation) | `useValidation.js` | Validation rules and rule generator |

## Props Factories

Many hooks export a `makeXxxProps` factory built with Vuetify's `propsFactory`. Use these when building components that accept the same props as a hook:

```js
import { makeModalProps } from '@/hooks/useModal'
import { makeRepeaterProps } from '@/hooks/useRepeater'
import { makeFilepondProps } from '@/hooks/useFilepond'
```

## Hook Layers

```
App state          useConfig · useUser · useLocale · useAuthorization · useCache
UI chrome          useSidebar · useNavigationLayout · useModal · useDynamicModal
Notifications      useAlert
Form               useForm · useFormBase · useInput · useModelValue · useValidation
Inputs             useFile · useImage · useFilepond · useRepeater · useInputFetch
                   useInputHandlers · useDraggable
Table              useTable · useActiveTableItem · useItemActions · useFormatter
Utilities          useCastAttributes · useModule · useRandKey · useRoot · useSvg
                   useInertiaRequests
```

## Table Sub-hooks

`useTable` is composed from 11 internal sub-hooks. See the [Table Sub-hooks Overview](/system-reference/frontend/composables/table/overview) for details.

| Sub-hook | Purpose |
|----------|---------|
| [useTableActions](/system-reference/frontend/composables/table/use-table-actions) | Toolbar / bulk action props |
| [useTableFilters](/system-reference/frontend/composables/table/use-table-filters) | Search, status tabs, advanced filters |
| [useTableForms](/system-reference/frontend/composables/table/use-table-forms) | Create/edit form state |
| [useTableGroup](/system-reference/frontend/composables/table/use-table-group) | Client-side column grouping |
| [useTableHeaders](/system-reference/frontend/composables/table/use-table-headers) | Column visibility and localStorage |
| [useTableItem](/system-reference/frontend/composables/table/use-table-item) | Edited item and soft-delete detection |
| [useTableItemActions](/system-reference/frontend/composables/table/use-table-item-actions) | Per-row action dispatch |
| [useTableIterator](/system-reference/frontend/composables/table/use-table-iterator) | Iterator (card/list) layout actions |
| [useTableModals](/system-reference/frontend/composables/table/use-table-modals) | Delete / custom / show modals |
| [useTableNames](/system-reference/frontend/composables/table/use-table-names) | i18n titles and dialog text |
| [useTableState](/system-reference/frontend/composables/table/use-table-state) | URL/localStorage state persistence |

## Utility Sub-hooks

Four small utility composables are available under `vue/src/js/hooks/utils/`. See the [Utils Overview](/system-reference/frontend/composables/utils/overview) for details.

| Sub-hook | Purpose |
|----------|---------|
| [useBadge](/system-reference/frontend/composables/utils/use-badge) | Badge visibility and props for action buttons |
| [useGenerate](/system-reference/frontend/composables/utils/use-generate) | Button prop generation with Inertia-aware href handling |
| [usePagination](/system-reference/frontend/composables/utils/use-pagination) | Infinite-scroll / load-more pagination state |
| [useSelect](/system-reference/frontend/composables/utils/use-select) | Select input prop definitions (`makeSelectProps`) |
