---
sidebarPos: 8
sidebarTitle: Custom Form Base (legacy)
---

# CustomFormBase (`v-custom-form-base`) <Badge type="warning" text="legacy" />

`CustomFormBase` is the original self-contained schema-driven form engine. It implements the same `VFormBase` API as [`FormBase`](./form-base) but with all rendering logic and helper methods inline in the component rather than split into a composable and a sub-component.

> [!WARNING]
> `CustomFormBase` is kept for backwards compatibility. For new work use [`FormBase`](./form-base) (`v-form-base`), which is the refactored version with the same API.

## API

`CustomFormBase` accepts exactly the same props, emits, schema syntax, slot naming convention, and supported field types as `FormBase`. Refer to the [FormBase documentation](./form-base) for the complete reference.

Key differences from `FormBase`:

| Aspect | FormBase | CustomFormBase |
|---|---|---|
| Logic location | `useFormBaseLogic` composable | Inline in the component's `methods` and `computed` |
| Field rendering | Delegated to `FormBaseField` | Inline `v-if/v-else-if` chain in the template |
| Composition API | `setup` returns `ctx` from composable | Options API with `getCurrentInstance` in `setup` |
| Nested self-reference | `v-form-base` | `v-custom-form-base` (references itself recursively) |
| Schema rebuild trigger | `watch` on `modelValue` keys via `__dot` + `onBeforeMount` | `watch` on `modelValue` keys via `__dot` in `created` |

## When to use

Use `CustomFormBase` only when:
- You are maintaining existing code that already imports `v-custom-form-base`.
- You need to avoid a refactor risk in a critical path.

In all other cases, use `FormBase`.
