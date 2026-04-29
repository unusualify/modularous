---
sidebarTitle: useTableNames
---

# useTableNames

Provides all translated title strings for a data table — the table header, form titles, delete dialog text, and subtitle — derived from the module name and the current i18n locale.

**File:** `vue/src/js/hooks/table/useTableNames.js`

---

## Props Factory

```js
import { makeTableNamesProps } from '@/hooks/table/useTableNames'
```

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `String` | — | Module name (snake_case), drives i18n key resolution |
| `moduleName` | `String` | — | Override the permission module name |
| `routeName` | `String` | — | Override the route name used for i18n |
| `customTitle` | `String` | — | Override the table header title (raw string, not translated) |
| `titlePrefix` | `String` | `''` | Prefix prepended to the table title |
| `titleKey` | `String` | `'name'` | Item property used as the item name in dialog text |
| `subtitle` | `String` | `''` | Table subtitle (translation key) |
| `formTitle` | `String` | — | Override both create and edit form titles |
| `formCreateTitleTranslationKey` | `String` | `'fields.new-item'` | i18n key for the create form title |
| `formEditTitleTranslationKey` | `String` | `'fields.edit-item'` | i18n key for the edit form title |
| `createFormTitle` | `String` | — | Override the create form title translation key |
| `editFormTitle` | `String` | — | Override the edit form title translation key |
| `formSubtitle` | `String` | — | Form subtitle (translation key) |
| `formCreateSubtitle` | `String` | — | Override for create-mode form subtitle |
| `formEditSubtitle` | `String` | — | Override for edit-mode form subtitle |

## Usage

```js
import useTableNames, { makeTableNamesProps } from '@/hooks/table/useTableNames'

const {
  tableTitle,
  tableSubtitle,
  formTitle,
  formSubtitle,
  transNameSingular,
  transNamePlural,
  deleteQuestion,
  deleteDialogTitle,
  deleteDialogDescription,
} = useTableNames(props, context)
```

## Returns

| Name | Type | Description |
|------|------|-------------|
| `snakeName` | `ComputedRef<String>` | Module name in snake_case |
| `permissionName` | `ComputedRef<String>` | Permission module name used in `can()` calls |
| `transNameSingular` | `ComputedRef<String>` | Translated singular module name (e.g. `'User'`) |
| `transNamePlural` | `ComputedRef<String>` | Translated plural module name (e.g. `'Users'`) |
| `transNameCountable` | `ComputedRef<String>` | Pluralized name with count (e.g. `'2 Users'`) |
| `tableTitle` | `ComputedRef<String>` | Table header title |
| `tableSubtitle` | `ComputedRef<String>` | Table subtitle |
| `formTitle` | `ComputedRef<String>` | Create or edit form title (switches based on `editedIndex`) |
| `formSubtitle` | `ComputedRef<String>` | Create or edit form subtitle |
| `deleteQuestion` | `ComputedRef<String>` | Confirmation question for delete dialog |
| `deleteDialogTitle` | `ComputedRef<String>` | Title for the delete confirmation dialog |
| `deleteDialogDescription` | `ComputedRef<String>` | Description text for the delete confirmation dialog |

## Notes

- `deleteQuestion`, `deleteDialogTitle`, and `deleteDialogDescription` use the item's `titleKey` property (default `'name'`) as the item label in the dialog text.
- For localized string values (e.g. `{ en: 'Product', tr: 'Ürün' }`), the current user locale is used to extract the display string.
- If a soft-deletable item (`deleted_at` set) is being deleted, the dialog uses `confirm-soft-deletion` keys instead of `confirm-deletion`.

## See Also

- [useModule](/system-reference/frontend/composables/use-module) — underlying i18n name resolution
- [useTableItem](/system-reference/frontend/composables/table/use-table-item) — provides `isSoftDeletableItem` used in dialog text selection
