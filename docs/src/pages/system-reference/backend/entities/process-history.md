---
sidebarPos: 16
sidebarTitle: ProcessHistory
---

# ProcessHistory

**File**: `src/Entities/ProcessHistory.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`

Audit trail for process status changes. Each record captures who changed the status, the new status, and an optional reason.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `process_id` | `int` | Parent process |
| `status` | `ProcessStatus` | The status recorded (enum cast) |
| `reason` | `string` | Optional reason for the change |
| `user_id` | `int` | User who made the change |

## Boot Events

| Event | Action |
|-------|--------|
| `created` | Dispatches `ProcessHistoryCreated` |
| `updated` | Dispatches `ProcessHistoryUpdated` |

## Relationships

### `process(): BelongsTo`

The parent [Process](./process) this history entry belongs to.

### `user(): BelongsTo`

The [User](./user) who triggered this status change.

## Table

Resolved from `modularous.tables.process_histories`, defaults to `m_process_histories`.

## Related

- [Process](./process) — the parent workflow instance
