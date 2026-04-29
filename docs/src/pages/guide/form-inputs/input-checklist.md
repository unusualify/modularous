---
sidebarPos: 8
sidebarTitle: Checklist
---

# Checklist

The `checklist` input type renders a multi-select checkbox list. It supports flat items, grouped/treeview layouts, and cascade filtering. The value stored is an array of selected `itemValue` values.

**Files:**
- PHP: `src/Hydrates/Inputs/ChecklistHydrate.php`
- Vue: `vue/src/js/components/inputs/Checklist.vue`

---

## Hydrate

**Class:** `ChecklistHydrate`  
**Config type:** `checklist`  
**Output type:** `input-checklist`

The hydrate sets `type` to `input-checklist` and applies the default requirements. Items can be provided statically or loaded via `connector`.

### Default Requirements

| Key | Default | Description |
|-----|---------|-------------|
| `itemValue` | `'id'` | Field used as the stored value |
| `itemTitle` | `'name'` | Field displayed for each item |
| `default` | `[]` | No items selected by default |
| `cascadeKey` | `'items'` | Key used when cascading filtered items |

### Config Usage

#### Static items

```php
[
    'type'      => 'checklist',
    'name'      => 'permissions',
    'label'     => 'Permissions',
    'items'     => [
        ['id' => 1, 'name' => 'Read'],
        ['id' => 2, 'name' => 'Write'],
        ['id' => 3, 'name' => 'Delete'],
    ],
]
```

#### Remote items via connector

```php
[
    'type'      => 'checklist',
    'name'      => 'category_ids',
    'label'     => 'Categories',
    'connector' => 'Blog:Category|repository:list',
]
```

#### With selected label

```php
[
    'type'          => 'checklist',
    'name'          => 'country_ids',
    'label'         => 'Select Countries',
    'selectedLabel' => 'Selected Countries',
    'connector'     => 'Location:Country|repository:list',
]
```

#### Treeview (grouped items)

Set `isTreeview: true` and ensure items have a nested `items` key for child groups.

```php
[
    'type'       => 'checklist',
    'name'       => 'region_ids',
    'label'      => 'Regions',
    'isTreeview' => true,
    'connector'  => 'Location:Region|repository:list:withs=children',
]
```

---

## Vue Component

**Component:** `VInputChecklist` (`v-input-checklist`)  
**File:** `vue/src/js/components/inputs/Checklist.vue`

Renders checkboxes in a responsive grid. Supports flat list, grouped treeview, card style, and `max` selection limit.

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Array` | `[]` | Selected values (array of `itemValue`) |
| `items` | `Array` | `[]` | List of item objects |
| `itemValue` | `String` | `'id'` | Key used as the checkbox value |
| `itemTitle` | `String` | `'name'` | Key used as the checkbox label |
| `label` | `String` | `null` | Input label shown above checkboxes |
| `subtitle` | `String` | `null` | Secondary label shown below the main label |
| `labelColor` | `String` | `'grey-darken-1'` | Label text color |
| `subtitleColor` | `String` | `'grey-darken-1'` | Subtitle text color |
| `disabled` | `Boolean` | `false` | Disable all checkboxes |
| `readonly` | `Boolean` | `false` | Make all checkboxes read-only |
| `isTreeview` | `Boolean` | `false` | Render items as a collapsible treeview |
| `isCard` | `Boolean` | `false` | Render each item as a card instead of a plain checkbox |
| `max` | `Number\|String` | `null` | Maximum number of selectable items |
| `mandatory` | `String` | `null` | Dot-path key on each item; if truthy, that item cannot be deselected |
| `flexColumn` | `Boolean` | `true` | Lay out label and checkboxes side-by-side on `md+` screens |
| `checkboxPosition` | `String` | `'right'` | `'left'` or `'right'` — side the checkbox icon appears |
| `checkboxHighlighted` | `Boolean` | `false` | Highlight selected checkbox rows with a background |
| `checkboxHighlightedColor` | `String` | `'grey-lighten-5'` | Background color when highlighted |
| `checkboxCol` | `Object` | `{ cols:3, sm:6, md:4, lg:3 }` | Vuetify column breakpoints for each checkbox |
| `orderBy` | `String` | `null` | Item key to sort by |
| `orderByDirection` | `String` | `'asc'` | `'asc'` or `'desc'` |
| `chunkField` | `String` | `null` | Group items by this field key |
| `chunkCharacter` | `String` | `'_'` | Delimiter used to auto-detect groups from `itemTitle` |
| `chunkTitleKey` | `String` | `'name'` | Key used when deriving group labels |
| `truncateItemLabel` | `Boolean` | `false` | Truncate long labels with ellipsis |
| `noGroupAllSelectable` | `Boolean` | `false` | Hide the "select all" checkbox on treeview group headers |
| `hasGroupBottomDivider` | `Boolean` | `true` | Show a divider below each group header in treeview |
| `openAllGroups` | `Boolean` | `false` | Expand all treeview groups on mount |
| `closeAllGroups` | `Boolean` | `false` | Collapse all treeview groups on mount |
| `cardStats` | `Array` | `[]` | Stat definitions `{ key, label }` shown inside each card item |
| `groupExpandGap` | `String` | `'4'` | Gap between treeview groups (Vuetify spacing unit) |
| `groupExpandTitleProps` | `Object` | `{}` | Extra props passed to group header title when `chunkField` is set |

### Behavior

- **Max selection** — when `max` is set (or inferred from `rules: 'max:N'`), checkboxes disable once the limit is reached. Mandatory items are always pre-selected and cannot be deselected.
- **Treeview** — when `isTreeview: true`, items must have a nested `items` array. Group headers get a "select all" checkbox; `openAllGroups` / `closeAllGroups` control initial state.
- **Card mode** — when `isCard: true`, each item renders as a `VInputCheckboxCard`. Use `cardStats` to show metric values inside each card.
- **Grouping without treeview** — when `chunkField` is set, flat items are grouped by that field value. Alternatively, `chunkCharacter` splits `itemTitle` on a delimiter to infer group names.

---

## See Also

- [Checklist Group](/guide/form-inputs/input-checklist-group) — Multiple checklist schemas in a single input
- [Forms Overview](/guide/form-inputs/overview) — Hydrate pipeline and schema contract
- [Relationships](/guide/generics/relationships) — Using `connector` to load remote data
