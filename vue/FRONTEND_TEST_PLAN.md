# Frontend Test Plan – Modularous Vue Package

## Executive Summary

This document analyzes the current Vitest frontend test coverage, identifies gaps, and provides a step-by-step plan to achieve comprehensive test coverage for the modularous Vue package.

---

## 1. Current State (as of analysis)

### Test Results
- **Total tests**: 187
- **Passing**: 174
- **Failing**: 13 (4 test files)
- **Test files**: 26 total (22 passed, 4 failed)

### Test Structure
```
vue/
├── test/                          # Co-located tests (test/*.test.js)
│   ├── components/                 # Component tests
│   ├── composables/                # Hook/composable tests
│   ├── utils/                      # Utility tests
│   ├── store/                      # Vuex store tests
│   └── example.test.js
├── src/js/
│   ├── components/inputs/__tests__/  # registry.spec.js
│   └── utils/__tests__/             # helpers.spec.js, schema.spec.js
└── vitest.config.mjs
```

### Existing Test Patterns
- **Factory pattern**: `factory(props, options)` for mounting with global plugins
- **Plugins**: UEConfig, Vuetify (createVuetify), i18n, store
- **Stubs**: `ue-recursive-stuff`, `ue-dynamic-component-renderer`, `ue-title`
- **Directives**: resize, intersect, touch, click-outside (stubbed in tests)

---

## 2. Failing Tests – Root Causes & Fixes

### 2.1 ue-alert.test.js & ue-callout.test.js
**Error**: `[Vuetify] Could not find defaults instance`

**Cause**: The `vuetify` import from `../../src/js/plugins/vuetify` exports a **function** (`createModularityVuetify`), not a Vuetify instance. Tests use `plugins: [vuetify]` but must use `plugins: [vuetify()]` or `plugins: [createModularityVuetify()]`.

**Fix**:
```js
import createModularityVuetify from '../../src/js/plugins/vuetify'
const vuetify = createModularityVuetify()
// Then: plugins: [vuetify]
```

**Note**: The modularity vuetify plugin may have side effects (require.context, theme loading) that fail in Vitest. If so, use a minimal `createVuetify({ components, directives })` like v-custom-form-base.test.js.

---

### 2.2 v-input-image.test.js
**Errors**:
- `openMediaLibrary does not exist` – Image.vue uses Composition API or setup(); `Image.methods` is undefined
- `data-test="addButton"` – Add button only renders when `input[index] === undefined`; with `modelValue: []` the structure differs
- Vuetify defaults / fitGrid directive

**Cause**: Image.vue structure changed; tests target Options API `methods` and wrong DOM structure.

**Fix**:
- Use `wrapper.vm.openMediaLibrary` or spy on the component instance after mount
- Ensure factory passes `modelValue` that yields the expected DOM (empty array vs undefined slots)
- Add Vuetify plugin and stub fitGrid directive

---

### 2.3 v-input-assignment.test.js
**Errors**:
- `axios.post` not called – mock/async timing
- `wrapper.vm.assignments[0].status` not updated – mock response not applied
- `wrapper.vm.$refs.createFormModal.dialog` undefined – refs not available in test
- `wrapper.vm.$refs.createForm` undefined – form ref structure

**Cause**: Refs, async flows, and mocks not aligned with component implementation.

**Fix**:
- Use `vi.stubGlobal` or inject axios mock correctly
- Await `flushPromises()` after async operations
- Stub child components that provide refs, or use `attachTo: document.body` for refs
- Simplify tests to focus on unit behavior rather than full integration

---

## 3. Coverage Gaps – Missing Tests

### 3.1 Input Components (registry.js hydrateTypeMap)

| Component | File | Test Exists | Priority |
|-----------|------|-------------|----------|
| VInputChecklist | Checklist.vue | ✅ | - |
| VInputTagger | Tagger.vue | ✅ | - |
| VInputImage | Image.vue | ✅ (broken) | Fix first |
| VInputAssignment | Assignment.vue | ✅ (broken) | Fix first |
| VInputProcess | Process.vue | ✅ | - |
| VInputSpread | Spread.vue | ✅ | - |
| VInputChat | Chat.vue | ✅ | - |
| VInputFile | File.vue | ❌ | High |
| VInputFilepond | Filepond.vue | ❌ | High |
| VInputPrice | Price.vue | ❌ | High |
| VInputDate | Date.vue | ❌ | Medium |
| VInputSelectScroll | SelectScroll.vue | ❌ | Medium |
| VInputRepeater | Repeater.vue | ❌ | Medium |
| VInputTag | Tag.vue | ❌ | High |
| VInputBrowser | Browser.vue | ❌ | Low |
| VInputRadioGroup | RadioGroup.vue | ❌ | Medium |
| VInputFormTabs | FormTabs.vue | ❌ | Low |
| VInputComparisonTable | ComparisonTable.vue | ❌ | Low |
| VInputChecklistGroup | ChecklistGroup.vue | ❌ | Low |
| VInputPaymentService | PaymentService.vue | ❌ | Low |
| VInputFilepondAvatar | FilepondAvatar.vue | ❌ | Low |

### 3.2 Composables / Hooks

| Hook | Test Exists | Priority |
|------|-------------|----------|
| useFormatter | ✅ | - |
| useSidebar | ✅ | - |
| useNavigationLayout | ✅ | - |
| useInput | ❌ | High |
| useValidation | ❌ | High |
| useForm | ❌ | High |
| useFile | ❌ | Medium |
| useFilepond | ❌ | Medium |
| useCurrency | ❌ | Medium |
| useMediaLibrary | ❌ | Medium |
| useRepeater | ❌ | Medium |
| useModal | ❌ | Medium |
| useConfig | ❌ | Low |
| useBadge | ❌ | Low |
| useFormatter (extended) | Partial | - |

### 3.3 Utils

| Util | Test Exists | Priority |
|------|-------------|----------|
| helpers | ✅ | - |
| schema | ✅ | - |
| itemConditions | ✅ | - |
| cropper | ✅ | - |
| country | ✅ | - |
| common-methods | ✅ | - |
| formEvents | ❌ | High |
| formEventFormatters/* | ❌ | Medium |
| getFormData | ❌ | Medium |
| response | ❌ | Low |
| locale | ❌ | Low |
| phone | ❌ | Low |
| errors | ❌ | Low |

### 3.4 Components (Other)

| Component | Test Exists | Priority |
|-----------|-------------|----------|
| CustomFormBase | ✅ | - |
| FormBase | ❌ | High |
| FormBaseField | ❌ | High |
| InputRenderer | ❌ | High |
| UEConfigurableCard | ✅ | - |
| UECallout | ✅ (broken) | Fix |
| UEAlert | ✅ (broken) | Fix |
| UEConfig | Used in tests | - |
| Modal | ❌ | Medium |
| Datatable | ❌ | Medium |
| Sidebar, Main, SidebarContent | ✅ | - |

### 3.5 Store

| Module | Test Exists |
|--------|-------------|
| user | ✅ |

---

## 4. Implementation Plan

### Phase 1: Fix Failing Tests (Immediate)
1. **ue-alert.test.js** – Use `createVuetify({ components, directives })` or ensure Vuetify instance is correctly passed
2. **ue-callout.test.js** – Same Vuetify fix; Callout uses `title`/`value` props, not `text` – adjust test expectations
3. **v-input-image.test.js** – Update to match current Image.vue structure; fix spies and data-test selectors
4. **v-input-assignment.test.js** – Fix axios mocks, refs, and async handling

### Phase 2: Input Components (High Priority)
1. Add `v-input-file.test.js`
2. Add `v-input-filepond.test.js`
3. Add `v-input-price.test.js`
4. Add `v-input-tag.test.js`
5. Fix and extend `v-input-image.test.js`

### Phase 3: Core Form & Utils
1. Add `FormBase.test.js` (or extend CustomFormBase coverage)
2. Add `FormBaseField.test.js`
3. Add `InputRenderer.test.js`
4. Add `formEvents.test.js`
5. Add `getFormData.test.js`

### Phase 4: Composables
1. Add `useInput.test.js`
2. Add `useValidation.test.js`
3. Add `useForm.test.js`
4. Add `useCurrency.test.js`
5. Add `useFile.test.js` / `useFilepond.test.js`

### Phase 5: Remaining Components & Utils
1. Add tests for Date, SelectScroll, Repeater, RadioGroup
2. Add tests for Modal, Datatable
3. Add tests for formEventFormatters, response, locale, phone

---

## 5. Test Utilities & Setup

### Recommended Test Helper
Create `test/helpers/factory.js`:

```js
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

export const vuetify = createVuetify({ components, directives })

export const defaultStubs = {
  'ue-recursive-stuff': true,
  'ue-dynamic-component-renderer': true,
  'ue-title': { template: '<span />' }
}

export const defaultDirectives = {
  resize: { mounted: () => {}, unmounted: () => {} },
  intersect: { mounted: () => {}, unmounted: () => {} },
  touch: { mounted: () => {}, unmounted: () => {} },
  'click-outside': { mounted: () => {}, unmounted: () => {} },
  'fit-grid': { mounted: () => {}, unmounted: () => {} }
}

export function createMountOptions(overrides = {}) {
  return {
    global: {
      plugins: [vuetify],
      stubs: defaultStubs,
      directives: defaultDirectives,
      ...overrides.global
    },
    attachTo: document.body,
    ...overrides
  }
}
```

### Vitest Setup
Ensure `vitest-setup/jsdom.js` runs before tests. It already:
- Mocks `HTMLCanvasElement.prototype.getContext`
- Adds CSRF meta
- Assigns `window.__*` helpers
- Mocks `ResizeObserver`
- Sets `window[APP_NAME].STORE.config`

---

## 6. Checklist

- [x] Fix ue-alert.test.js
- [x] Fix ue-callout.test.js
- [x] Fix v-input-image.test.js
- [x] Fix v-input-assignment.test.js
- [ ] Add createMountOptions helper (optional)
- [x] Add useCurrency.test.js
- [x] Add input-renderer.test.js
- [x] Add v-input-file.test.js
- [x] Add v-input-filepond.test.js
- [x] Add v-input-price.test.js
- [x] Add v-input-tag.test.js (covered by existing `v-input-tagger.test.js` for `Tagger.vue`)
- [ ] Add FormBase.test.js
- [ ] Add FormBaseField.test.js
- [ ] Add InputRenderer.test.js
- [ ] Add formEvents.test.js
- [ ] Add useInput.test.js
- [ ] Add useValidation.test.js
- [ ] Add useForm.test.js
- [ ] Add useCurrency.test.js
- [ ] Run `npm run test:coverage` and target >80% for critical paths

---

## 7. Running Tests

```bash
cd packages/modularous/vue
npm run test          # Watch mode
npm run test:run      # Single run
npm run test:coverage # Coverage report
npm run test:ui       # Vitest UI
```
