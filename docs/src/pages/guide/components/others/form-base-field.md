---
sidebarPos: 7
sidebarTitle: Form Base Field
---

# FormBaseField

`FormBaseField` is the internal field-renderer sub-component of [`FormBase`](./form-base). It receives a single schema field descriptor (`obj`) and a `ctx` handle to the parent's `useFormBaseLogic` composable, then selects and renders the appropriate Vuetify or custom component.

> [!NOTE]
> This is an internal component of `FormBase`. You do not use it directly. Customise output through `FormBase` slots.

## Props

| Prop | Type | Required | Description |
|---|---|---|---|
| `obj` | `Object` | Yes | Field descriptor `{ key, value, schema }` from the flattened combined array |
| `ctx` | `Object` | Yes | The `useFormBaseLogic` composable context returned from `FormBase.setup` |
| `index` | `Number` | Yes | Field position in the sorted array |
| `formItem` | `Object` | No | Forwarded to `preview` type fields via `ue-recursive-stuff` |

## Rendering decision tree

Fields are matched in priority order:

| Condition | Rendered as |
|---|---|
| `type === 'preview'` and `schema.configuration` set | `ue-recursive-stuff` |
| `type === 'dynamic-component'` | `ue-dynamic-component-renderer` |
| `type === 'title'` | Mapped component via registry |
| `type === 'radio'` | Mapped component with `options` and inject slots |
| `isDateTimeColorTypeAndExtensionText(obj)` | `v-menu` wrapping a `v-text-field` activator + a date/time/color picker |
| `type === 'array'` | `div` loop + nested `v-form-base` per item |
| `type === 'group'` or `'wrap'` | Container component (default `v-card`) + nested `v-form-base` |
| `type === 'treeview'` | `VTreeview` (from `vuetify/labs/VTreeview`) |
| `type === 'list'` | `v-list` with optional toolbar label |
| `type === 'checkbox'` or `'switch'` | Mapped component |
| `type === 'file'` | `v-file-input` |
| `type === 'icon'` | `v-icon` |
| `type === 'slider'` | `v-slider` |
| `type === 'img'` | `v-img` |
| `type === 'btn-toggle'` | `v-btn-toggle` with option buttons |
| `type === 'btn'` | `v-btn` |
| `schema.translated` is set | `v-input-locale` |
| `schema.mask` is set | Mapped component with `v-mask` directive |
| Default | Mapped component via registry |

## Type mapping

`ctx.mapTypeToComponent(type)` resolves a schema type string to a Vue component using the global input registry. Custom types registered via `registerInputType` are also resolved here.

## Group / Wrap containers

For `group` and `wrap` types, the wrapping component is resolved via `ctx.checkInternGroupType(obj)`:
- Uses `obj.schema.typeInt` if set.
- Falls back to `v-card`.
- Accepts any `v-*` or `ue-*` prefixed component name.

Optional `title` and `subtitle` strings are rendered above the nested form using `ue-title`.

## Slot passthrough

`FormBaseField` passes all of `$slots` into nested `v-form-base` instances (for `array` and `group`/`wrap` types), and into each rendered component via `getInjectedScopedSlots` / `getKeyInjectSlot` for inject slots. This is how parent `FormBase` slots reach deeply nested fields.
