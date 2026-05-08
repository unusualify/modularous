---
sidebarPos: 19
sidebarTitle: ProcessController
---

# ProcessController

**File**: `src/Http/Controllers/ProcessController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers`  
**Extends**: `Illuminate\Routing\Controller`

Manages process workflow instances — retrieving their state with optional eager-loaded relationships and updating their status, reason, or processable-model fields.

## Methods

### `show(Request $request, Process $process): JsonResponse`

Returns a process and its relationships.

**Request parameters**:

| Parameter | Type | Description |
|-----------|------|-------------|
| `with` | `array` | Relationship names to eager-load |

Only relationships defined in the process model's `$eagerLoadable` property are permitted. Invalid relationships are silently ignored.

### `update(Request $request, Process $process): JsonResponse`

Updates a process. Two modes:

**Status/reason update** — when `status` or `reason` is present:

| Field | Description |
|-------|-------------|
| `status` | New process status value |
| `reason` | Optional reason text |

**Processable field update** — when the request contains fields from the processable model's schema:

Fields are extracted by matching request keys against the processable model's form schema. Only schema-declared fields are written; all others are rejected.

Non-eager-loadable relations are loaded via `$process->load()` after the update so the response always contains fresh relationship data.

## Process Model

The `Process` model represents a state-machine instance attached to a `processable` morphable model (e.g. an Order, a Subscription). The processable model's fields can be updated directly through this controller when the process is in a state that permits editing.

## Related

- `HasProcess` entity trait — adds process management to any model
