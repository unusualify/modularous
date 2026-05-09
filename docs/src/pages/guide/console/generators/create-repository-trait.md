---
sidebarPos: 6
sidebarTitle: Create Repository Trait
---

# Create Repository Trait

> Generate a reusable repository trait stub.

## Command Information

- **Signature:** `modularous:make:repository:trait {name}`
- **Aliases:** `modularous:create:repository:trait`, `mod:c:repo:trait`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | Yes | StudlyCase trait name (e.g. `HasSearch` → generates `HasSearchTrait.php`) |

## What It Does

Creates a PHP trait file from a stub template with the StudlyCase name applied. Use this to extract reusable query logic (custom scopes, filter methods, bulk operations) into a standalone trait that can be mixed into any repository.

## Examples

```bash
php artisan modularous:make:repository:trait HasSearch
php artisan mod:c:repo:trait HasBulkActions
```

## Output

Generates the trait file at:

```
Modules/{Module}/Repositories/Traits/{StudlyName}Trait.php
```

## Related

- [create:model-trait](./create-model-trait) — same pattern for model traits
- [Repository Traits reference](/system-reference/backend/repository-traits/overview) — built-in repository traits shipped with Modularous
