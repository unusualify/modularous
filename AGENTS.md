# Project Instructions

You are an expert in Modularity package development. This is the unusualify/modularity Laravel package repository.

## CRITICAL DISTINCTION
- ❌ DO NOT: Explain how to create modules (that's for users)
- ✅ DO: Develop the Modularity package itself (src/ directory)

## PACKAGE STRUCTURE

src/                      # Package source code (work here)
├── Console               # Artisan commands
├── Hydrates/             # Schema hydrators (InputHydrator → *Hydrate)
│   └── Inputs/           # Input-specific hydrates (type → schema)
├── Http/Controllers/     # Controllers
├── Providers/           # Service providers
├── Repositories/        # Repository pattern
├── Services/            # Business logic
├── Traits/              # Reusable traits
└── Entities/            # Models
vue/src/                 # Frontend source
├── js/components/       # Vuetify components
├── js/hooks/            # Vue composables
├── js/utils/            # Utilities (helpers, schema, etc.)
└── js/store/            # Vuex store

## PATTERNS TO ALWAYS USE
2. **Use Traits**: ManageMedias, HasMedias, MediasTrait etc.
3. **Register in ServiceProvider**: Every new feature if necessary
4. **Write Tests**: tests/$FOLDERNAME
5. **Type Hints**: Always use PHP 8.1+ type hints
6. **Config-Driven**: Use config('modularity.xxx') (under merges folder)

## EXAMPLE REQUESTS
"Add versioning to entities" → Create src/Entities/Traits/HasVersioning.php
"Improve DataTable component" → Edit vue/src/components/Table/DataTable.vue
"Add --with-media flag to make:entity" → Edit src/Console/EntityMakeCommand.php

## CODE GENERATION RULES
- Always use Repository pattern (never direct model access)
- Always use Service layer for business logic if necessary
- Always add PHPDoc comments
- Always write corresponding tests
- Use Vue 3 Composition API for frontend
- Use Vuetify 3 components (not plain HTML)

## WHEN ADDING FEATURES
1. Create class in appropriate src/ subdirectory
2. Register in ModularityServiceProvider
3. Write unit + feature tests
4. Update documentation

## FORBIDDEN
- ❌ Business logic in controllers
- ❌ new keyword (use DI)
- ❌ Hard-coded paths (use config)
- ❌ Options API in Vue (use Composition API)
- ❌ Plain HTML (use Vuetify components)
- ❌ window.__* helpers in new code (use import from @/utils/helpers)

## HELPERS
- Prefer `import { isObject, dataGet } from '@/utils/helpers'` over `window.__isObject`, `window.__data_get`
- window.__* is deprecated; kept for backward compatibility during migration

---

## HYDRATE ↔ INPUT ADAPTER

The backend (PHP Hydrates) and frontend (Vue Inputs) communicate via a **schema contract**. Hydrates produce schema; Input components consume it.

### Data Flow

```
Module config (type: 'checklist') → InputHydrator → ChecklistHydrate → schema { type: 'input-checklist', ... }
                                                                              ↓
FormBase/FormBaseField → mapTypeToComponent('input-checklist') → VInputChecklist (Checklist.vue)
```

### Naming Convention

| Hydrate class      | Config type | Output type (schema) | Vue component   | File              |
|--------------------|-------------|----------------------|-----------------|-------------------|
| ChecklistHydrate  | checklist   | input-checklist      | VInputChecklist | Checklist.vue     |
| TaggerHydrate      | tagger      | input-tagger         | VInputTagger    | Tagger.vue        |
| SelectHydrate      | select      | select (or input-select-scroll) | v-select | (Vuetify) |
| FileHydrate        | file        | input-file           | VInputFile      | File.vue          |
| ImageHydrate       | image       | input-image          | VInputImage     | Image.vue         |
| ...                | ...         | input-{kebab}        | VInput{Studly}  | {Studly}.vue      |

- **Hydrate**: `studlyName($input['type']) . 'Hydrate'` → e.g. `checklist` → `ChecklistHydrate`
- **Output type**: Hydrate sets `$input['type'] = 'input-{kebab}'` (e.g. `input-checklist`)
- **Vue component**: `registerComponents(..., 'inputs', 'VInput')` → `Checklist.vue` → `VInputChecklist`
- **Resolution**: `mapTypeToComponent('input-checklist')` → `v-input-checklist` (kebab of VInputChecklist)

### When Adding a New Input

1. **PHP**: Create `src/Hydrates/Inputs/{Studly}Hydrate.php` extending `InputHydrate`
   - Set `$input['type'] = 'input-{kebab}'` in `hydrate()`
   - Define `$requirements` for default schema keys
2. **Vue**: Create `vue/src/js/components/inputs/{Studly}.vue`
   - Use `useInput`, `makeInputProps`, `makeInputEmits` from `@/hooks`
   - Component registers as `VInput{Studly}` via `includeFormInputs` glob
3. **Registry** (optional): Add to `hydrateTypeMap` in `registry.js` for explicit mapping

### Schema Contract

Vue inputs expect schema props via `obj.schema` or `boundProps`:

- **Common**: `name`, `label`, `default`, `rules`, `items`, `itemValue`, `itemTitle`
- **Selectable**: `cascadeKey`, `cascades`, `repository`, `endpoint`
- **Files**: `accept`, `maxFileSize`, `translated`, `max`
- **Hydrate-only** (stripped before frontend): `route`, `model`, `repository`, `cascades`, `connector`

### Hydrate Types → Vue Components

See `vue/src/js/components/inputs/registry.js` → `hydrateTypeMap` for the full mapping.

Always ask for clarification if the request is ambiguous.
