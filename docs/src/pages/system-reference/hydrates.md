---
sidebarPos: 3
sidebarTitle: Hydrates
---

# Hydrates

Hydrates transform module config into frontend schema. The backend (PHP) and frontend (Vue) communicate via a **schema contract**: hydrates produce schema; input components consume it.

## Flow

```
Module config (type: 'checklist') → InputHydrator → ChecklistHydrate → schema { type: 'input-checklist', ... }
                                                                              ↓
FormBase/FormBaseField → mapTypeToComponent('input-checklist') → VInputChecklist (Checklist.vue)
```

1. **Module config** defines inputs with `type` (e.g. `checklist`, `select`, `price`)
2. **InputHydrator** resolves: `studlyName($input['type']) . 'Hydrate'` → e.g. `ChecklistHydrate`
3. **Hydrate** sets `$input['type'] = 'input-{kebab}'` and enriches schema (items, endpoint, rules, etc.)
4. **render()** pipeline: `setDefaults()` → `hydrate()` → `hydrateRecords()` → `hydrateRules()` → strips backend-only keys
5. **Frontend** receives schema via Inertia; FormBaseField uses `mapTypeToComponent(type)` → Vue component

## Resolution

| Config type | Hydrate class | Output type (schema) | Vue component |
|-------------|---------------|----------------------|---------------|
| assignment | AssignmentHydrate | input-assignment | VInputAssignment |
| authorize | AuthorizeHydrate | select | v-select (Vuetify) |
| chat | ChatHydrate | input-chat | VInputChat |
| checklist | ChecklistHydrate | input-checklist | VInputChecklist |
| creator | CreatorHydrate | input-browser | VInputBrowser |
| date | DateHydrate | input-date | VInputDate |
| file | FileHydrate | input-file | VInputFile |
| filepond | FilepondHydrate | input-filepond | VInputFilepond |
| image | ImageHydrate | input-image | VInputImage |
| payment-service | PaymentServiceHydrate | input-payment-service | VInputPaymentService |
| price | PriceHydrate | input-price | VInputPrice |
| process | ProcessHydrate | input-process | VInputProcess |
| repeater | RepeaterHydrate | input-repeater | VInputRepeater |
| select | SelectHydrate | select | v-select (Vuetify) |
| spread | SpreadHydrate | input-spread | VInputSpread |
| stateable | StateableHydrate | select | v-select (Vuetify) |
| tagger | TaggerHydrate | input-tagger | VInputTagger |
| ... | ... | input-{kebab} | VInput{Studly} |

**Rule**: `studlyName($input['type']) . 'Hydrate'` → class in `src/Hydrates/Inputs/`

## Hydrate Output Types (registry.js)

| Output type | Vue component |
|-------------|---------------|
| input-assignment | VInputAssignment |
| input-browser | VInputBrowser |
| input-chat | VInputChat |
| input-checklist | VInputChecklist |
| input-checklist-group | VInputChecklistGroup |
| input-comparison-table | VInputComparisonTable |
| input-date | VInputDate |
| input-file | VInputFile |
| input-filepond | VInputFilepond |
| input-filepond-avatar | VInputFilepondAvatar |
| input-form-tabs | VInputFormTabs |
| input-image | VInputImage |
| input-payment-service | VInputPaymentService |
| input-price | VInputPrice |
| input-process | VInputProcess |
| input-radio-group | VInputRadioGroup |
| input-repeater | VInputRepeater |
| input-select-scroll | VInputSelectScroll |
| input-spread | VInputSpread |
| input-tag | VInputTag |
| input-tagger | VInputTagger |

## Schema Contract

**Common keys** (frontend expects): `name`, `label`, `default`, `rules`, `items`, `itemValue`, `itemTitle`, `col`, `disabled`, `creatable`, `editable`

**Selectable**: `cascadeKey`, `cascades`, `repository`, `endpoint`

**Files**: `accept`, `maxFileSize`, `translated`, `max`

**Hydrate-only** (stripped before frontend): `route`, `model`, `repository`, `cascades`, `connector`

## Adding a New Input

1. **PHP**: Create `src/Hydrates/Inputs/{Studly}Hydrate.php` extending `InputHydrate`
   - Set `$input['type'] = 'input-{kebab}'` in `hydrate()` (or `select` for select-based hydrates like AuthorizeHydrate, StateableHydrate)
   - Define `$requirements` for default schema keys
2. **Vue**: Create `vue/src/js/components/inputs/{Studly}.vue`
   - Use `useInput`, `makeInputProps`, `makeInputEmits` from `@/hooks`
   - Component registers as `VInput{Studly}` via `includeFormInputs` glob
3. **Registry** (optional): Add to `hydrateTypeMap` in `registry.js` for explicit mapping

See the [create-input-hydrate](/guide/commands/Generators/create-input-hydrate) and [create-vue-input](/guide/commands/Generators/create-vue-input) commands.
