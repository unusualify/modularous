---
sidebarTitle: useFormatter
---

# useFormatter

Provides named column value formatters for data tables. Each formatter transforms a raw cell value into a render descriptor (tag, attributes, text) that the table cell component renders.

**File:** `vue/src/js/hooks/useFormatter.js`  
**Props factory:** `makeFormatterProps`

---

## Usage

```js
import { useFormatter, makeFormatterProps } from '@/hooks'

const props = defineProps({ ...makeFormatterProps() })
const { formatterColumns, handleFormatter } = useFormatter(props, context, headers)
```

```html
<!-- In a table cell slot -->
<template #item.status="{ value }">
  <u-formatter :config="handleFormatter(['status'], value)" />
</template>
```

## Props (via `makeFormatterProps`)

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `ignoreFormatters` | `Array` | `[]` | List of formatter names to skip for specific columns |

## Returns

| Name | Type | Description |
|------|------|-------------|
| `formatterColumns` | `ComputedRef<Array>` | Headers that have a `formatter` or `formatterName` property |
| `handleFormatter` | `(formatter, value) => Object` | Resolves a formatter by name and returns a render config |
| `dateFormatter` | `Function` | Formats ISO date strings using `vue-i18n` `d()` |
| `chipFormatter` | `Function` | Wraps value in a `v-chip` config |
| `badgeFormatter` | `Function` | Wraps value in a `v-badge` config |
| `statusFormatter` | `Function` | Renders a green ✓ / red ✗ icon based on truthiness |
| `shortenFormatter` | `Function` | Truncates a string to `max` characters |
| `priceFormatter` | `Function` | Renders price with currency unit and optional tax |
| `pascalFormatter` | `Function` | Converts a string to PascalCase |
| `editFormatter` | `Function` | Wraps value in a clickable `<span>` |

## Formatter config in table headers

Define `formatter` on a header object in your module config:

```php
[
    'key'       => 'status',
    'title'     => 'Status',
    'formatter' => ['status'],        // name only
],
[
    'key'       => 'created_at',
    'title'     => 'Created',
    'formatter' => ['date', 'short'], // name + additional args
],
[
    'key'       => 'price',
    'title'     => 'Price',
    'formatter' => ['price', '₺'],
],
```

`date` formats come from `datetimeFormats` in `vue/src/js/config/i18n.js`:

| Format | en example |
|---|---|
| `numeric` | 3/20/2026 |
| `numeric-full` | 3/20/2026, 8:43:58 AM |
| `short` | Mar 20, 26 |
| `medium` | Mar 20, 2026 |
| `medium-time` | Mar 20, 2026, 8:43:58 AM |
| `long` | Friday, March 20, 2026 at 8:43 AM |

A header with `'visible' => false` stays available in the column picker but is hidden until the user enables it. Once the user saves a column selection for the page (localStorage `table_unvisible_columns_{path}`), that selection wins over the config.

## Render descriptor shape

`handleFormatter` returns:

```js
{
  configuration: {
    tag: 'v-chip',          // optional; defaults to plain text
    attributes: { ... },    // optional; passed to the tag
    elements: 'cell value'  // inner content
  }
}
```

## See Also

- [Data Tables](/guide/components/shared/data-tables) — how formatters are declared in the table config
