---
sidebarPos: 2
sidebarTitle: Input Time
---

# InputTime <Badge type="warning" text="experimental" />

`InputTime` presents a read-only text field that opens a `v-time-picker` in a popover when clicked. The field value and the picker are both bound to the same model value.

## Schema usage

```php
[
  'type'        => 'time',
  'name'        => 'starts_at',
  'label'       => 'Start Time',
  'picker_props' => [
    'format'    => '24hr',
  ],
]
```

## Schema keys

| Key | Type | Description |
|---|---|---|
| `label` | `String` | Label shown on the text field |
| `picker_props` | `Object` | Props forwarded directly to `v-time-picker` (e.g. `format`, `min`, `max`, `use-seconds`) |
| Any other key | — | Forwarded to the backing `v-text-field` via `obj.schema` |

## Value format

The stored value is a time string in `HH:MM` (or `HH:MM:SS` with `use-seconds`) format, matching what `v-time-picker` emits on `@click:minute`.

## Notes

- The popover closes automatically when the user selects the minutes digit.
- The text field is `readonly` — users cannot type a time directly; they must use the picker.
- `picker_props` is bound via `$bindAttributes`, so Vuetify attribute inheritance rules apply.
