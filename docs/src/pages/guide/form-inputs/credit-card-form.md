---
sidebarTitle: Credit Card Form
sidebarPos: 12
---

# Credit Card Form

`CreditCardForm` is a complete payment card entry UI: a live animated card preview (`CreditCard`) paired with a form for card number, holder name, expiry date, and CVV. There is no corresponding PHP hydrate — these components are used directly in Vue templates, typically inside the [Payment Service](/guide/form-inputs/input-payment-service) flow.

## Vue Component — CreditCardForm

**File:** `vue/src/js/components/inputs/CreditCardForm.vue`

The form injects `submitForm` from its parent context via `inject('submitForm')` — the parent is responsible for providing the submit handler.

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `formData` | `Object` | `{ cardName, cardNumber, cardNumberNotMask, cardMonth, cardYear, cardCvv }` | Reactive form data object; mutated directly by the form |
| `backgroundImage` | `String \| Object` | — | Custom card background image URL |
| `randomBackgrounds` | `Boolean` | `true` | Randomly pick a background from the built-in image set |
| `inputDensity` | `String` | `'default'` | Vuetify density for all text fields (`'compact'`, `'comfortable'`, `'default'`) |

### Emits

| Event | Payload | Description |
|-------|---------|-------------|
| `update:cardName` | `String` | Card holder name changed |
| `update:cardNumber` | `String` | Card number changed (masked) |
| `update:cardMonth` | `String` | Expiry month changed |
| `update:cardYear` | `Number` | Expiry year changed |
| `update:cardCvv` | `String` | CVV changed |

### Usage

```vue
<script setup>
import { reactive, provide } from 'vue'

const formData = reactive({
  cardName: '',
  cardNumber: '',
  cardNumberNotMask: '',
  cardMonth: '',
  cardYear: '',
  cardCvv: '',
})

provide('submitForm', () => {
  // handle payment submission
})
</script>

<template>
  <CreditCardForm
    :formData="formData"
    :randomBackgrounds="true"
    inputDensity="comfortable"
  />
</template>
```

### Behaviour

- The card number is **auto-masked** (middle digits replaced with `*`) when the field loses focus.
- Card type (Visa, Mastercard, Amex, etc.) is detected from the card number prefix and the corresponding logo is shown on the card preview.
- The expiry year dropdown generates 12 years from the current year. If the selected year equals the current year, past months are disabled.
- The PAY button calls the injected `submitForm` function.

---

## Sub-component — CreditCard

**File:** `vue/src/js/components/inputs/CreditCard.vue`

Pure display component that renders the animated 3D card. Used internally by `CreditCardForm`; can also be used standalone.

### Props

| Prop | Type | Description |
|------|------|-------------|
| `labels` | `Object` | `{ cardNumber, cardName, cardMonth, cardYear, cardCvv }` — current display values |
| `fields` | `Object` | `{ cardNumber, cardName, cardMonth, cardYear, cardCvv }` — element IDs for focus tracking |
| `isCardNumberMasked` | `Boolean` | Whether to mask middle digits of the card number |
| `randomBackgrounds` | `Boolean` | Randomly select a background image |
| `backgroundImage` | `String \| Object` | Custom background override |

The card automatically flips to show the CVV side when the CVV field has focus.

## See Also

- [Payment Service](/guide/form-inputs/input-payment-service) — Full payment method selector that uses this form
- [Price](/guide/form-inputs/input-price) — Numeric price input with currency selection
