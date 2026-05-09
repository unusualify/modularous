---
sidebarPos: 15
sidebarTitle: Process
---

# Process

**File**: `src/Entities/Process.php`  
**Namespace**: `Unusualify\Modularous\Entities`  
**Extends**: `Illuminate\Database\Eloquent\Model`  
**Traits**: `ProcessScopes`

Tracks a state-machine workflow instance attached to a parent model via a morph relation. The `ProcessStatus` enum drives the available statuses and their presentation (labels, colours, icons, dialog copy).

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `processable_id` | `int` | Parent model ID |
| `processable_type` | `string` | Parent model class |
| `status` | `ProcessStatus` | Current status (enum cast) |
| `reason` | `string` | Optional reason for the current status |

## Relationships

### `processable(): MorphTo`

The parent model this process belongs to.

### `histories(): HasMany`

All status-change history records for this process.

### `lastHistory(): HasOne`

The most recent history entry (`latest()` on `created_at`).

## Accessors

| Accessor | Type | Source |
|----------|------|--------|
| `status_label` | `string` | `ProcessStatus::label()` |
| `status_color` | `string` | `ProcessStatus::color()` |
| `status_icon` | `string` | `ProcessStatus::icon()` |
| `status_card_variant` | `string` | `ProcessStatus::cardVariant()` |
| `status_card_color` | `string` | `ProcessStatus::cardColor()` |
| `status_reason_label` | `string` | `ProcessStatus::statusReasonLabel()` |
| `status_informational_message` | `string` | `ProcessStatus::informationalMessage()` |
| `next_action_label` | `string` | `ProcessStatus::nextActionLabel()` |
| `next_action_color` | `string` | `ProcessStatus::nextActionColor()` |
| `status_dialog_titles` | `array` | Title for each status value |
| `status_dialog_messages` | `array` | Confirmation messages for each status transition |

## ProcessStatus Enum Values

`PREPARING`, `WAITING_FOR_CONFIRMATION`, `WAITING_FOR_REACTION`, `REJECTED`, `CONFIRMED`

## Related

- [ProcessHistory](./process-history) — audit trail for status changes
- [ProcessController](/system-reference/backend/http/controllers/process-controller) — process management endpoints
- [HasProcesses](/system-reference/backend/entity-traits/processes/has-processes) — adds process support to models
