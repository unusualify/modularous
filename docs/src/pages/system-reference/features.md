---
sidebarPos: 13
sidebarTitle: Features Pattern
---

# Features Pattern

Modularity features use a **triple pattern**: Entity trait + Repository trait + Hydrate. Understanding this pattern helps when adding or customizing features.

## Pattern Overview

```mermaid
flowchart LR
    Config[Route config type: file]
    Hydrate[FileHydrate]
    Schema[schema type: input-file]
    Repo[FilesTrait]
    Model[HasFiles]
    
    Config --> Hydrate
    Hydrate --> Schema
    Schema --> Repo
    Repo --> Model
```

1. **Route config** defines input with `type` (e.g. `file`, `image`, `repeater`)
2. **Hydrate** transforms to frontend schema (`input-file`, etc.)
3. **Repository trait** handles persistence in `hydrate*Trait`, `afterSave*Trait`, `getFormFields*Trait`
4. **Entity trait** provides model relationships and accessors

## Entity Trait (Model)

- **Location**: `src/Entities/Traits/Has*.php` or `*.php` (e.g. `Assignable`)
- **Purpose**: Relationships, boot logic, accessors, scopes
- **Convention**: `HasX` for "has many/one X"; `IsX` for behavior (e.g. `IsSingular`); `Xable` for "can be X'd" (e.g. `Assignable`, `Processable`)

**Example — HasFiles**:
- `files()` — morphToMany File with pivot (role, locale)
- `file($role, $locale)` — URL for first file
- `filesList($role, $locale)` — array of URLs
- `fileObject($role, $locale)` — File model

## Repository Trait

- **Location**: `src/Repositories/Traits/*Trait.php`
- **Purpose**: Persistence hooks called by Repository lifecycle
- **Convention**: `setColumns*Trait`, `hydrate*Trait`, `afterSave*Trait`, `getFormFields*Trait`

**Example — FilesTrait**:
- `setColumnsFilesTrait` — registers file columns from inputs with `type` containing `file`
- `hydrateFilesTrait` — sets `$object->files` relation from form data
- `afterSaveFilesTrait` — syncs pivot (attach/updateExistingPivot)
- `getFormFieldsFilesTrait` — loads existing files into form fields

## Hydrate

- **Location**: `src/Hydrates/Inputs/*Hydrate.php`
- **Purpose**: Transform module config into frontend schema
- **Convention**: `$input['type'] = 'input-{kebab}'` (e.g. `input-file`, `input-assignment`); some hydrates output `select` (e.g. AuthorizeHydrate, StateableHydrate); set `name`, `label`, `items`, `endpoint`, etc.

**Example — FileHydrate**:
- `requirements`: `name` => `files`, `translated` => false, `default` => []
- `hydrate()`: `type` → `input-file`, `label` → `__('Files')`

## Adding a New Feature

1. **Entity trait**: Add `HasMyFeature` with relationships and accessors
2. **Repository trait**: Add `MyFeatureTrait` with `hydrate*`, `afterSave*`, `getFormFields*`
3. **Hydrate**: Add `MyFeatureHydrate` extending `InputHydrate`; set `type` → `input-my-feature`
4. **Vue component**: Create `VInputMyFeature`; register in `registry.js`
5. **Config**: Add trait to `modularity.traits` if needed; add input to route config

## Feature Dependencies

Some features compose others:
- **HasRepeaters** uses HasFiles, HasImages, HasPriceable, HasFileponds
- **HasPayment** uses HasPriceable
- **Processable** uses HasFileponds

## See Also

- [Module Features Overview](/guide/module-features/) — Feature matrix and quick reference
- [Hydrates](/system-reference/hydrates) — Schema transformation
- [Repositories](/system-reference/repositories) — Lifecycle and traits
- [Entities](/system-reference/entities) — Entity traits list
