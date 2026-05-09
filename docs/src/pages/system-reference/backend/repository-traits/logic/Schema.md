---
sidebarPos: 12
sidebarTitle: Schema
---

# Schema

**Namespace**: `Unusualify\Modularous\Repositories\Logic\Schema`

Manages the active input schema for a repository operation and provides helpers to chunk inputs into flat arrays. Composes `ManageTraits`.

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$schema` | `array\|null` | The overriding schema set for the current operation. When `null`, `inputs()` is used. |

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `setSchema` | `(?array $schema): void` | Overrides the active schema for the duration of the current operation (e.g., a specific form context) |
| `getSchema` | `(): array\|null` | Returns the currently active overriding schema |
| `getInputs` | `(): array` | Returns `$schema` if set, otherwise falls back to `$this->inputs()` |
| `getRawInputs` | `(): array` | Always returns `$this->inputs()` — ignores any `$schema` override |
| `getRawChunkedInputs` | `(bool $all, bool $noGroupChunk): array` | Chunks `getRawInputs()` via `chunkInputs()` |
| `getChunkedInputs` | `(bool $all, bool $noGroupChunk): array` | Chunks `getInputs()` (respects `$schema` override) via `chunkInputs()` |

## Chunking Behaviour

`chunkInputs()` flattens the inputs array into a single-level associative array keyed by input `name`. The `$all` flag includes inputs that are normally hidden (e.g. conditional inputs). The `$noGroupChunk` flag prevents group-level chunking, returning all inputs regardless of group boundaries.

## Usage

```php
// Temporarily override schema for a specific operation
$repo->setSchema($customSchema);
$fields = $repo->getFormFields($object);
$repo->setSchema(null); // restore

// Get all chunked inputs for column detection
$inputs = $repo->getRawChunkedInputs(all: true);
```
