---
sidebarTitle: useBadge
---

# useBadge

Determines whether an action button should show a badge and returns the badge props to spread onto `<v-badge>`.

**File:** `vue/src/js/hooks/utils/useBadge.js`

---

## Usage

```js
import useBadge from '@/hooks/utils/useBadge'

const { isBadge, badgeProps } = useBadge(props, context)
```

```html
<v-badge v-if="isBadge(action)" v-bind="badgeProps(action)">
  <v-icon>{{ action.icon }}</v-icon>
</v-badge>
```

## Returns

| Name | Signature | Description |
|------|-----------|-------------|
| `isBadge` | `(action: Object) => Boolean` | Returns `true` when the action has a `badge` property with a truthy / non-zero value |
| `badgeProps` | `(action: Object) => Object` | Returns `{ content, color, textColor }` ready to spread onto `<v-badge>` |

## Badge Action Fields

| Field | Type | Description |
|-------|------|-------------|
| `badge` | `Boolean\|Number\|String` | Badge visibility / content. Numeric strings are parsed — `'0'` hides the badge. |
| `badgeContent` | `any` | Badge label (falls back to `badge` if not set) |
| `badgeColor` | `String` | Badge background color (default `'warning'`) |
| `badgeTextColor` | `String` | Badge text color (default `'white'`) |

## See Also

- [useTableItemActions](/system-reference/frontend/composables/table/use-table-item-actions) — action dispatch that uses `useBadge`
