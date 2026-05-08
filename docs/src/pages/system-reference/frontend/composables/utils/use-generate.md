---
sidebarTitle: useGenerate
---

# useGenerate

Generates Vuetify button props from an action definition, with Inertia-aware `href` handling and responsive icon-only collapse on mobile.

**File:** `vue/src/js/hooks/utils/useGenerate.js`

---

## Usage

```js
import useGenerate from '@/hooks/utils/useGenerate'

const { generateButtonProps, generatedButtonProps } = useGenerate(props, context)
```

```html
<!-- Spread onto any v-btn -->
<v-btn v-bind="generateButtonProps(action)">{{ action.label }}</v-btn>

<!-- Or use the reactive computed version (reads from props directly) -->
<v-btn v-bind="generatedButtonProps">{{ props.label }}</v-btn>
```

## Returns

| Name | Type | Description |
|------|------|-------------|
| `generateButtonProps` | `(action: Object) => Object` | Generate button props from an action definition object |
| `generatedButtonProps` | `ComputedRef<Object>` | Reactive button props computed from `props` (used when the component itself is an action) |

## Generated Props

`generateButtonProps(action)` returns:

| Prop | Source | Description |
|------|--------|-------------|
| `icon` | `action.icon` (when `!forceLabel`) | Icon to display |
| `text` | `action.label` (when `forceLabel`) | Text label |
| `color` | `action.color` | Button color |
| `variant` | `action.variant` | Vuetify variant |
| `density` | `action.density` | Default `'comfortable'` |
| `size` | `action.size` | Default `'default'` |
| `disabled` | `action.disabled` | Disabled state |
| `rounded` | `true` (icon), `null` (label) | Rounded style |
| `onClick` | Inertia-aware handler | Set when `action.href` is provided |

## Inertia Href Handling

When `action.href` is set, the generated `onClick` handler:

1. Calls `e.preventDefault()` on the click event
2. If `shouldUseInertia` is `true` and the URL is on the same origin → `router.visit(href)`
3. If target is not `'_blank'` → `router.visit(href, { target })`
4. Otherwise → `window.open(href, target)`

## Responsive Behavior

`generatedButtonProps` collapses to icon-only on `xs` screens (Vuetify's `smAndUp` breakpoint is `false`):
- Sets `icon` to the resolved icon value
- Switches `density` to `'compact'`
- Sets `rounded: true`

## See Also

- [useConfig](/system-reference/frontend/composables/use-config) — provides `shouldUseInertia`
- [useTableItemActions](/system-reference/frontend/composables/table/use-table-item-actions) — calls `generateButtonProps` for row actions
