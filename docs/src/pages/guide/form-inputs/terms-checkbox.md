---
sidebarTitle: Terms Checkbox
sidebarPos: 37
---

# Terms Checkbox

`VInputTermsCheckbox` is a checkbox that gates agreement behind a terms modal. The user must open and read the terms/conditions dialog before the checkbox can be checked. It is used on registration and payment flows. There is no corresponding PHP hydrate.

## Vue Component

**Registered as:** `VInputTermsCheckbox`
**File:** `vue/src/js/components/inputs/TermsCheckbox.vue`

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | — | — | Current checkbox value (v-model) |
| `label` | `String` | `'I agree to the terms and conditions'` | Default label shown next to the checkbox |
| `htmlLabel` | `String` | — | Raw HTML label; overrides `label` when set |
| `terms` | `String` | *(i18n `authentication.terms-policy`)* | HTML content shown in the Terms modal tab |
| `conditions` | `String` | *(i18n `authentication.conditions-policy`)* | HTML content shown in the Conditions modal tab |
| `trueValue` | `Boolean \| String \| Number` | `1` | Value stored when checked |
| `falseValue` | `Boolean \| String \| Number` | `0` | Value stored when unchecked |
| `noCheckbox` | `Boolean` | `false` | Hides the checkbox element (keeps the label and modal) |
| `noHandleClick` | `Boolean` | `false` | Disables the forced-read flow; allows direct toggle |

### Emits

Standard `makeInputEmits` — `update:modelValue`.

### Slots

| Slot | Scope | Description |
|------|-------|-------------|
| `label` | `labelScope` | Override the entire label content |

### Usage

```vue
<!-- Default — forces user to read terms before checking -->
<VInputTermsCheckbox
  v-model="form.agreed"
  :terms="termsHtml"
  :conditions="conditionsHtml"
/>
```

```vue
<!-- Custom HTML label with links -->
<VInputTermsCheckbox
  v-model="form.agreed"
  htmlLabel="I agree to the <a href='/terms'>Terms</a> and <a href='/privacy'>Privacy Policy</a>"
/>
```

```vue
<!-- Hidden checkbox (agreement implied by another action) -->
<VInputTermsCheckbox
  v-model="form.agreed"
  :noCheckbox="true"
  :noHandleClick="true"
/>
```

### Behaviour

- **First click**: Opens the terms modal instead of toggling the checkbox. Once opened, `isRead` is set to `true`.
- **Subsequent clicks**: Toggles the checkbox normally.
- The modal has two sections accessible via the default label — **Terms** and **Conditions** — each opening the same modal with the respective content.
- The modal's confirm button ("I agree") sets the checkbox to `trueValue` and closes the dialog.
- A validation rule is applied after 2 interaction attempts: the checkbox must be checked (`trueValue`) to pass.
- `noHandleClick` bypasses the forced-read flow entirely — the checkbox behaves like a standard `v-checkbox`.

## See Also

- [Checkbox](/guide/form-inputs/input-checkbox) — Simple boolean toggle without terms flow
- [Forms overview](/guide/form-inputs/overview) — Schema-driven form architecture
