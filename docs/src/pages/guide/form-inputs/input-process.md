---
sidebarPos: 27
sidebarTitle: Process
---

# Process

The `process` input type renders `VInputProcess`, a stateful process tracker card. It displays the current status of a processable record, allows authorised roles to advance the process through its lifecycle (preparing → waiting\_for\_confirmation → confirmed → etc.), and optionally surfaces an inline form for editing the processable entity's data.

> [!IMPORTANT]
> The model associated with the route must use the `Processable` trait. The hydrate throws if this trait is absent.

## Hydrate

**Class:** `ProcessHydrate`
**Config type:** `process`
**Output type:** `input-process` → `VInputProcess`

The hydrate:
- Requires `_moduleName` and `_routeName` to be set in the config pipeline
- Validates the route model has `Unusualify\Modularity\Entities\Traits\Processable`
- Auto-resolves `fetchEndpoint` → `route('admin.process.show', [:id, eager?])`
- Auto-resolves `updateEndpoint` → `route('admin.process.update', [:id])`
- Overrides `name` to `process_id`

## Usage

### Minimal

```php
[
    'type' => 'process',
]
```

### With eager-loaded relationships

```php
[
    'type'  => 'process',
    'eager' => ['documents', 'approvals'],
]
```

### With role-gated actions and custom status config

```php
[
    'type'               => 'process',
    'processEditableRoles' => ['superadmin', 'manager'],
    'actionRoles'        => [
        'confirmed' => ['superadmin'],
        'rejected'  => ['superadmin', 'manager'],
    ],
    'statusConfiguration' => [
        'preparing' => [
            'title'              => 'Preparing',
            'icon'               => 'mdi-progress-clock',
            'color'              => 'secondary',
            'next_action_label'  => 'Submit for Review',
            'next_action_color'  => 'primary',
            'dialog_title'       => 'Submit for Review?',
            'dialog_confirm_text'=> 'Submit',
            'dialog_cancel_text' => 'Cancel',
            'response_message'   => 'Submitted successfully.',
        ],
    ],
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `color` | `'grey'` | Card colour when no status colour is set |
| `cardVariant` | `'outlined'` | Vuetify card variant |
| `processableTitle` | `'name'` | Field used as the process card title |
| `eager` | `[]` | Relationships to eager-load with the process |
| `name` | `'process_id'` | Form field name (set by hydrate, not config) |

## See Also

- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
