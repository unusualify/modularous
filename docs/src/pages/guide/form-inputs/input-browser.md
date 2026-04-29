---
sidebarPos: 4
sidebarTitle: Browser
---

# Browser

The `browser` input type opens an inline record browser that lets users search and pick records from another module route. Used internally by the **Creator** feature and can appear in any form.

**Files:**
- PHP: `src/Hydrates/Inputs/BrowserHydrate.php`
- Vue: `vue/src/js/components/inputs/Browser.vue`

---

## Hydrate

**Class:** `BrowserHydrate`  
**Config type:** `browser`  
**Output type:** `input-browser`

When `_moduleName` and `_routeName` are both set, the hydrate resolves `endpoint` automatically from the module's index action URL. Otherwise, provide `endpoint` directly.

### Default Requirements

| Key | Default | Description |
|-----|---------|-------------|
| `itemValue` | `'id'` | Field used as the record identifier |
| `itemTitle` | `'name'` | Field displayed for each record |
| `default` | `null` | Default selected value |
| `returnObject` | `false` | Return the full object instead of just the value |
| `label` | `'Browser'` | Input label |
| `multiple` | `false` | Allow selecting multiple records |
| `max` | `null` | Maximum selectable records (when `multiple: true`) |
| `objectModelValues` | `['*']` | Model attributes to include in the returned object |
| `objectIdDefiner` | `null` | Custom attribute to use as the record identifier |

### Config Usage

#### Basic — auto-resolved endpoint

```php
[
    'type'        => 'browser',
    'name'        => 'author_id',
    'label'       => 'Author',
    '_moduleName' => 'Blog',
    '_routeName'  => 'Author',
]
```

#### Manual endpoint

```php
[
    'type'     => 'browser',
    'name'     => 'author_id',
    'label'    => 'Author',
    'endpoint' => '/admin/blog/authors',
]
```

#### Multiple selection with a limit

```php
[
    'type'        => 'browser',
    'name'        => 'author_ids',
    'label'       => 'Authors',
    '_moduleName' => 'Blog',
    '_routeName'  => 'Author',
    'multiple'    => true,
    'max'         => 3,
]
```

---

## Vue Component

**Component:** `VInputBrowser` (`v-input-browser`)  
**File:** `vue/src/js/components/inputs/Browser.vue`

Opens a paginated search dialog to browse and select records from a remote endpoint. Supports single and multiple selection, `returnObject` mode, and preserves initial values across re-renders.

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `*` | — | Selected value or array of values |
| `endpoint` | `String` | — | API URL to fetch records from |
| `itemValue` | `String` | `'id'` | Key used as the value for each record |
| `itemTitle` | `String` | `'name'` | Key displayed for each record |
| `multiple` | `Boolean` | `false` | Allow selecting multiple records |
| `returnObject` | `Boolean` | `false` | Emit the full object instead of only the value |
| `objectIdDefiner` | `String` | — | Override which key identifies a record when `returnObject` is true |
| `objectModelValues` | `Array` | `['*']` | Keys to include from the record when `returnObject` is true |
| `convertObject` | `Boolean` | `false` | Convert returned object to a flat value after selection |
| `items` | `Array` | `[]` | Pre-populate the browser list (skips initial fetch) |
| `page` | `Number` | `1` | Starting page |
| `lastPage` | `Number` | `-1` | Total page count (set by server response) |
| `itemsPerPage` | `Number` | `20` | Records per page |
| `with` | `Array` | `[]` | Eager-load relations on each record |
| `scopes` | `Array` | `[]` | Query scopes to apply on the server |
| `orders` | `Array` | `[]` | Order definitions `{ key, direction }` |
| `appends` | `Array` | `[]` | Model appends to include in the response |
| `column` | `Array` | `[]` | Columns to select |
| `searchKeys` | `Array` | — | Keys the search field filters by |
| `variant` | `String` | `'outlined'` | Vuetify input variant |
| `density` | `String` | `'comfortable'` | Vuetify input density |
| `rules` | `String\|Array` | `[]` | Validation rules |
| `useFullUrl` | `Boolean` | `false` | Use the full absolute URL when building requests |
| `preserveInitialValues` | `Boolean` | `true` | Keep previously selected values when the list reloads |

### Behavior

- Opens a **dialog** with a paginated, searchable list of records fetched from `endpoint`.
- On open, if `modelValue` already contains IDs, those records are fetched by `ids` param and shown as pre-selected.
- **Pagination** — loads the next page via infinite scroll or page navigation; appends results to the existing list.
- **Return modes** — when `returnObject: false` (default), emits the `itemValue` of selected records; when `returnObject: true`, emits the full record object filtered by `objectModelValues`.
- **Multiple** — when `multiple: false`, selecting a record immediately closes the dialog and emits the value.

---

## See Also

- [Forms Overview](/guide/form-inputs/overview) — Hydrate pipeline and schema contract
- [Creator feature](/guide/module-features/creator) — How `BrowserHydrate` is used by the Creator pattern
