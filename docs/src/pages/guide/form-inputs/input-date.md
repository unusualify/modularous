---
sidebarPos: 13
sidebarTitle: Date
---

# Date

The `date` input type renders `VInputDate`, a date-picker powered by Vuetify's `v-date-input`. It handles timezone offset correction automatically so the date stored on the server matches the user's selected calendar date regardless of browser timezone.

## Hydrate

**Class:** `DateHydrate`
**Config type:** `date`
**Output type:** `input-date` → `VInputDate`

The hydrate sets `type` to `input-date`. No default schema keys are set by the hydrate — all styling is passed through `$attrs`.

## Usage

### Basic date picker

```php
[
    'type'  => 'date',
    'name'  => 'published_at',
    'label' => 'Publish Date',
]
```

### With validation rules

```php
[
    'type'  => 'date',
    'name'  => 'due_date',
    'label' => 'Due Date',
    'rules' => 'required',
]
```

### Timezone-aware (no offset correction)

By default the component subtracts the browser's UTC offset before emitting the value, keeping the stored date consistent across timezones. Set `useTimezone: true` to disable this behaviour and emit the raw selected date.

```php
[
    'type'        => 'date',
    'name'        => 'event_date',
    'label'       => 'Event Date',
    'useTimezone' => true,
]
```

## Schema Defaults

No hydrate-level defaults. The component accepts all standard Vuetify `v-date-input` attributes via `$attrs`.

| Prop | Default | Description |
|------|---------|-------------|
| `variant` | `'outlined'` | Vuetify field variant |
| `density` | `'default'` | Vuetify density |
| `useTimezone` | `false` | When `false`, corrects UTC offset before emitting |

## See Also

- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
