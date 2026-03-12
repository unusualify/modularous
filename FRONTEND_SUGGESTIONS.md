# Frontend Enhancement Suggestions

For your review when you're back. Respond to what you'd like to prioritize.

---

## 1. Options API → Composition API Migration

**Current**: ~90% of components use Options API; AGENTS.md mandates Composition API.

**Suggestions**:
- Migrate high-traffic components first: Form.vue, Auth.vue, Datatable.vue
- Add ESLint rule to enforce Composition API for new components
- Create migration script for incremental conversion

---

## 2. Replace Mixins with Composables

**Current**: Mixins (Locale, Modal, Input, MediaLibrary) overlap with composables.

**Suggestions**:
- Audit `grep -r "mixins:" vue/src`
- Replace each mixin with equivalent composable
- ~~Deprecate `mixins/` folder~~ ✓ Done – refactored to hooks, folder removed

---

## 3. Complete CustomFormBase Extraction

**Current**: Input Registry and InputRenderer exist; CustomFormBase still has inline type blocks.

**Suggestions**:
- Extract each schema type (preview, title, radio, array, wrap/group, etc.) into `schema-types/Input*.vue`
- CustomFormBase becomes a loop over `<InputRenderer :schema="obj" />` with slot passthrough
- Reduces CustomFormBase from ~1400 to ~200 lines

---

## 4. Add CSRF Meta to Vitest Setup

**Current**: Some tests fail because `document.querySelector('meta[name="csrf-token"]')` returns null in jsdom.

**Suggestions**:
- Add `<meta name="csrf-token" content="test">` to vitest-setup jsdom
- Or mock Document in affected tests

---

## 5. TypeScript Migration

**Current**: All .js and .vue; no type safety.

**Suggestions**:
- Migrate utils/helpers.js to TypeScript first
- Add types for store, API responses
- Incremental migration for new files

---

## 6. Labs Component Flag

**Current**: `components/labs/` mixed with production.

**Suggestions**:
- Add `VUE_ENABLE_LABS=true` to conditionally load labs in build
- Exclude labs from production bundle when flag is false

---

## 7. Replace window.__* Usage in Componables

**Current**: Many composables still use `window.__isObject`, `window.__isset`, etc.

**Suggestions**:
- Replace with `import { isObject, isset } from '@/utils/helpers'` in composables
- Remove window.__* assignments from init.js once migration is complete

---

## 8. Vuex → Pinia Migration

**Current**: Vuex 4; Pinia recommended for Vue 3.

**Suggestions**:
- See docs/PINIA_MIGRATION.md
- Plan for v4.x; create Pinia stores alongside Vuex

---

**Priority order** (suggested): 4 (fix tests) → 7 (finish helpers migration) → 1 (Composition API) → 3 (CustomFormBase extraction) → 2 (mixins) → 5 (TypeScript) → 6 (labs) → 8 (Pinia)
