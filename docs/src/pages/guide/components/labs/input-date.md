---
sidebarPos: 1
sidebarTitle: Input Date
---

# InputDate <Badge type="warning" text="experimental" />

`InputDate` wraps a native `<input type="date">` field inside the `ue-form` schema system. It normalises the value to an ISO date string (`YYYY-MM-DD`) and strips schema keys that conflict with the native date input (`offset`, `order`, `type`).

## Schema usage

```php
[
  'type'  => 'date',
  'name'  => 'published_at',
  'label' => 'Publication Date',
]
```

## Value format

| Direction | Format |
|---|---|
| Model → field | ISO string converted to `YYYY-MM-DD` via `Date.toISOString().split('T')[0]` |
| Field → model | Raw `YYYY-MM-DD` string as returned by the native date input |

An empty model value renders an empty field (no default date is assumed).

## Locale display

The computed `dateFormattedLocale` property formats the stored value using `$d(date, 'medium')` from Vue I18n, but this is not currently rendered in the template — it is available for use in custom slot overrides or parent components.

## Notes

- The `type`, `offset`, and `order` keys are omitted from the props forwarded to `v-text-field` to prevent conflicts with the native `type="date"` attribute.
- No date-range constraints (`min` / `max`) are configured by default; pass them through the schema object.

```php
[
  'type'  => 'date',
  'name'  => 'start_date',
  'label' => 'Start Date',
  'min'   => '2024-01-01',
  'max'   => now()->toDateString(),
]
```
