---
sidebarPos: 2
sidebarTitle: Assignment
---

# Assignment

The `assignment` input type renders `VInputAssignment`, a task-assignment widget that lets authorised users assign a record to another user, track due dates, add descriptions, and view assignment history. It is backed by a full assignment lifecycle (create → confirm → complete).

## Hydrate

**Class:** `AssignmentHydrate`
**Config type:** `assignment`
**Output type:** `input-assignment` → `VInputAssignment`

The hydrate:
- Defaults `name` to `assignable_id`; `noSubmit: true` (the field never submits its value directly)
- Resolves `assigneeType` from the target module's model when not explicitly set
- Fetches `items` (the list of possible assignees) via `assigneeType::query()`, optionally scoped by `scopeRole`
- Auto-resolves `fetchEndpoint` → `{module}.{route}.assignments` and `saveEndpoint` → `{module}.{route}.createAssignment`
- Builds a Filepond schema for attachments (PDF by default, up to 3 files, 10 MB each)
- Requires both `_moduleName` and `_routeName` to be available in the config pipeline

## Usage

### Minimal (auto-resolved)

```php
[
    'type' => 'assignment',
]
```

### Restrict assignees by role

```php
[
    'type'      => 'assignment',
    'scopeRole' => ['manager', 'admin'],
]
```

### Explicit assignee type and file limits

```php
[
    'type'               => 'assignment',
    'assigneeType'       => \App\Models\User::class,
    'assignableType'     => \App\Models\Task::class,
    'maxFileSize'        => '5MB',
    'max-attachments'    => 5,
    'acceptedExtensions' => ['pdf', 'jpg', 'png'],
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `name` | `'assignable_id'` | Form field name |
| `noSubmit` | `true` | Prevents the field from being submitted directly |
| `col` | `{cols: 12}` | Always full width |
| `default` | `null` | No pre-selected assignee |
| `authorizedRoles` | `['superadmin', 'admin']` | Roles that can create/view assignments |
| `minDueDays` | `0` | Minimum days ahead a due date can be set |

## Vue Component

**Registered as:** `VInputAssignment`
**File:** `vue/src/js/components/inputs/Assignment.vue`

### Additional Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | `Array` | `[]` | List of possible assignees (pre-loaded by hydrate) |
| `fetchEndpoint` | `String` | `null` | URL to fetch existing assignments (`:id` replaced at runtime) |
| `saveEndpoint` | `String` | `null` | URL to create/update assignments (`:id` replaced at runtime) |
| `assignableType` | `String` | `null` | Fully-qualified model class of the record being assigned |
| `assigneeType` | `String` | `null` | Fully-qualified model class of the assignee |
| `authorizedRoles` | `Array` | `['superadmin','admin']` | Roles that can create and view assignments |
| `minDueDays` | `Number` | `0` | Minimum days ahead for the due date |
| `filepond` | `Object` | `null` | Filepond schema for attachment uploads |
| `variant` | `String` | `'outlined'` | Vuetify field variant |
| `density` | `String` | `'default'` | Vuetify field density |

### Behaviour

- On mount, fetches existing assignments from `fetchEndpoint` and displays the most recent one.
- The **Assign** button (shown to authorized roles) opens a modal to create a new assignment with assignee, due date, description, and optional attachments.
- The **History** button opens a scrollable list of all past assignments.
- `noSubmit: true` — the field value is never included in the standard form submission; all data is sent via direct Axios calls to the save endpoint.

## See Also

- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
