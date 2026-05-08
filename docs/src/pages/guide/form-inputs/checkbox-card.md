---
sidebarTitle: Checkbox Card
sidebarPos: 7
---

# Checkbox Card

`VInputCheckboxCard` is a card-style selectable item used for multi-select UIs. Clicking the card toggles the value in/out of the `modelValue` array. There is no corresponding PHP hydrate — this component is used directly in Vue templates.

## Vue Component

**Registered as:** `VInputCheckboxCard`
**File:** `vue/src/js/components/inputs/CheckboxCard.vue`

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Array` | — | Array of currently selected values (v-model) |
| `value` | `String \| Number` | `null` | The value this card represents; toggled in/out of `modelValue` |
| `title` | `String` | *(required)* | Card heading text |
| `description` | `String` | `''` | Optional body text below the title |
| `stats` | `Array` | `null` | Array of `{ label, value, color? }` stat blocks shown at the bottom |
| `disabled` | `Boolean` | `false` | Disables click and dims the card |
| `readonly` | `Boolean` | `false` | Prevents value changes without dimming |
| `checkboxColor` | `String` | `'primary'` | Vuetify color of the embedded checkbox |
| `activeColor` | `String` | `null` | Card background color when selected |
| `activeTitleColor` | `String` | `null` | Title text color when selected |
| `checkboxOnRight` | `Boolean` | `false` | Places the checkbox in the card's append slot instead of prepend |

### Usage

```vue
<VInputCheckboxCard
  v-model="selectedPlans"
  :value="plan.id"
  :title="plan.name"
  :description="plan.description"
  :stats="[
    { label: 'Users', value: plan.users, color: 'primary' },
    { label: 'Storage', value: plan.storage, color: 'success' },
  ]"
  checkboxColor="success"
/>
```

### Behaviour

- The component operates on an **array** `modelValue`. Clicking toggles `value` in or out of that array using `Array.includes` / `Array.filter`.
- When `value` is in `modelValue` the card switches to `elevated` variant and applies `border-primary`; otherwise it uses `outlined` with `border-grey-lighten-4`.
- `stats` renders a responsive grid of metric blocks inside a `v-card-text` at the bottom of the card. Each stat takes equal column width.

## See Also

- [Checkbox](/guide/form-inputs/input-checkbox) — Simple boolean toggle (single value)
- [Forms overview](/guide/form-inputs/overview) — Schema-driven form architecture
