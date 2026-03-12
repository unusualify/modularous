---
sidebarPos: 2
---

# Forms

Modularity forms are schema-driven. The backend hydrates module config into a schema; the frontend renders it via FormBase and FormBaseField.

## Flow

1. **Module config** — Define inputs in your module's `config.php` (see [Hydrates](/system-reference/hydrates))
2. **Controller** — `setupFormSchema()` hydrates the schema before create/edit
3. **Inertia** — Schema and model are passed to the page
4. **Form.vue** — Receives `schema` and `modelValue`, uses `useForm`
5. **FormBase** — Flattens schema + model into `flatCombinedArraySorted`, iterates over each field
6. **FormBaseField** — Renders each field by `obj.schema.type` via `mapTypeToComponent()`
7. **Input components** — Receive schema props via `bindSchema(obj)`

## Key Components

| Component | Purpose |
|-----------|---------|
| **Form.vue** | Top-level form; validation, submit, schema/model sync |
| **FormBase** | Iterates over flattened schema; grid layout, slots |
| **FormBaseField** | Renders a single field; resolves type → component |
| **CustomFormBase** | Wrapper with app-specific behavior |

## Schema Structure

Each field in the schema has:

- `type` — Resolved to Vue component (e.g. `input-checklist`, `text`, `select`)
- `name` — Field name (binds to model)
- `label` — Display label
- `col` — Grid column span
- `rules` — Validation rules
- `default` — Default value

See [Schema Contract](/system-reference/hydrates#schema-contract) for full keys. For config → schema flow per feature, see [Module Features Overview](/guide/module-features/).

## Slots

FormBase provides slots for customization:

- `form-top`, `form-bottom` — Form-level
- `{type}-top`, `{type}-bottom` — By schema type (e.g. `input-checklist-top`)
- `{key}-top`, `{key}-bottom` — By field name
- `{type}-item`, `{key}-item` — Override field rendering

## Adding Custom Inputs

1. Create Vue component in `vue/src/js/components/inputs/`
2. Register: `registerInputType('input-my-type', 'VInputMyType')`
3. Create PHP Hydrate in `src/Hydrates/Inputs/` (for backend schema)

See [Adding a New Input](/system-reference/api#adding-a-new-input-type).
