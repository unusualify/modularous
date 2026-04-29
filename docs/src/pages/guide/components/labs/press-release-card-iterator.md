---
sidebarPos: 11
sidebarTitle: Press Release Card Iterator
---

# PressReleaseCardIterator <Badge type="warning" text="experimental" />

`PressReleaseCardIterator` is a domain-specific card row component used as a custom iterator inside a `ue-table` that displays press release records. It renders a `ue-configurable-card` with a fixed four-column layout for package languages, content details, price, and status.

> [!NOTE]
> This component is tightly coupled to the press release data model. It is not a general-purpose iterator.

## Usage inside a data table

Register it as the `row-iterator` on a `ue-table`:

```html
<ue-table
  :columns="columns"
  :items="items"
  row-iterator="press-release-card-iterator"
>
  <template #actions>
    <v-btn icon="mdi-pencil" @click="edit(item)" />
  </template>
</ue-table>
```

## Card layout

| Segment | Content |
|---|---|
| Header | Press release ID (`item.id`) and headline (`item.content.headline`) |
| Segment 1 | Package → languages map rendered as a `ue-property-list` |
| Segment 2 | Content file, media images, and distribution date |
| Segment 3 | Price (`item._price`) |
| Segment 4 | Status string (`item._status`, defaults to `'Draft'`) |
| Actions | Delegated to the `#actions` slot |

## Expected item shape

```js
{
  id: 123,
  name: 'Q1 Announcement',
  content: {
    headline: 'Company announces Q1 results',
    file: 'release.pdf',
    press_release_images: [{ image: 'photo.jpg' }],
    date: '2025-04-17',
  },
  press_release_packages: {
    pkg1: {
      name: 'Premium',
      packageLanguages: [{ name: 'English' }, { name: 'German' }],
    },
  },
  _price: '$2,500',
  _status: 'Published',
}
```

## Slots

| Slot | Description |
|---|---|
| `actions` | Rendered inside the card's actions segment. Use for edit, delete, or view buttons. |

## Props

Props are defined by `makeTableIteratorProps()` from `@/hooks/table`. Standard iterator props include the `item` object and any formatter configuration. `ignoreFormatters` is accepted and defaults to excluding the `activate` formatter.
