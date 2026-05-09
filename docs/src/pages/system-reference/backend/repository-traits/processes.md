---
sidebarPos: 6
sidebarTitle: Process & Repeater Traits
---

# Process & Repeater Repository Traits

These traits handle workflow process management and nested repeater block persistence. `ProcessableTrait` auto-creates approval processes, while `RepeatersTrait` manages JSON repeater blocks with locale and media support.

---

## ProcessableTrait

**Namespace**: `Unusualify\Modularous\Repositories\Traits\ProcessableTrait`

Auto-creates a workflow `Process` record for models that use the `Processable` entity trait. Hydrates the process ID and any nested process schema fields into form fields.

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `setColumnsProcessableTrait` | Registers inputs matching `type: process` |
| `getFormFieldsProcessableTrait` | Sets each process column to the model's process ID (auto-creating the process if needed). If the process input has a nested `schema`, hydrates those sub-fields as well. |

### Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getProcessId` | `(Model $object, string $status = 'preparing'): mixed` | Returns the existing process ID, or creates a new `Process` with the given status and returns its ID |

### Form Field Hydration Flow

```
getFormFieldsProcessableTrait($object, $fields, $schema)
  └─ for each process column:
       ├─ $fields[$column] = getProcessId($object)   // ensures Process exists
       └─ if input has nested schema:
            ├─ collect schema inputs not already in $fields
            └─ call getFormFields() recursively for those inputs
```

This recursive hydration allows process inputs to contain embedded sub-forms (e.g., approval notes, status fields) that are hydrated alongside the process ID.

### Usage

```php
use Unusualify\Modularous\Repositories\Traits\ProcessableTrait;

class SubmissionRepository extends Repository
{
    use ProcessableTrait;
}

// Form input detected from schema:
// ['type' => 'process', 'name' => 'approval_process', 'schema' => [...]]

// Process is auto-created on first form load
$processId = $repo->getProcessId($submission);
```

---

## RepeatersTrait

**Namespace**: `Unusualify\Modularous\Repositories\Traits\RepeatersTrait`

Persists nested repeater blocks (JSON content stored in a `Repeater` morph-many) with full locale support. Internally composes `FilesTrait`, `ImagesTrait`, and `PricesTrait` to handle media and pricing within repeater rows.

### Composed Traits

```php
trait RepeatersTrait
{
    use FilesTrait, ImagesTrait, PricesTrait;
    // ...
}
```

This composition allows repeater blocks to contain file uploads, image attachments, and price inputs that are automatically detected and persisted by their respective traits.

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `setColumnsRepeatersTrait` | Registers inputs matching `type: json-repeater` or `root: json-repeater` from the raw input schema |
| `afterSaveRepeatersTrait` | Creates or updates `Repeater` records per role and locale |
| `getFormFieldsRepeatersTrait` | Loads repeater content from the database and maps it back into form fields |

### After-Save Flow

For each repeater column:

1. Determines if the content is **translated** (from schema `translated` flag).
2. Detects if the submitted data is **locale-keyed** (associative keys matching system locales).

**Translated repeaters:**
- Iterates all system locales.
- Creates or updates a `Repeater` record per locale with `{role, content, locale}`.

**Non-translated repeaters:**
- Uses the fallback locale.
- Creates or updates a single `Repeater` record.

### Form Field Hydration

When loading form fields for an existing record:

1. Groups existing repeaters by locale.
2. For each repeater:
   - **Translated** — maps content into `fields[$role][$locale]` using `Arr::dot()` / `Arr::set()`.
   - **Non-translated** — maps content into `fields[$role]` directly.
3. When no repeater data exists, initializes with empty arrays (locale-keyed if translated, or the input's `default` value).

### Helper Method

| Method | Signature | Description |
|--------|-----------|-------------|
| `getRepeaterInputs` | `(?array $schema): array` | Filters raw inputs to return only those with `type: json-repeater` or `root: json-repeater`, enriched with their `translated` flag |

### Usage

```php
use Unusualify\Modularous\Repositories\Traits\RepeatersTrait;

class ProductRepository extends Repository
{
    use RepeatersTrait;
}

// Form schema with repeater:
// [
//     'type' => 'json-repeater',
//     'name' => 'features',
//     'translated' => true,
//     'schema' => [
//         ['type' => 'text', 'name' => 'title'],
//         ['type' => 'image', 'name' => 'icon'],
//         ['type' => 'price', 'name' => 'addon_price'],
//     ]
// ]

// Get detected repeater inputs
$inputs = $repo->getRepeaterInputs();
```
