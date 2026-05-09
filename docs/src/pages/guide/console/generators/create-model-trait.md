---
sidebarPos: 5
sidebarTitle: Create Model Trait
---

# Create Model Trait

> Generate a reusable Eloquent model trait stub.

## Command Information

- **Signature:** `modularous:make:model:trait {name}`
- **Aliases:** `modularous:create:model:trait`, `mod:c:model:trait`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | Yes | StudlyCase trait name (e.g. `HasAnalytics` → generates `HasAnalyticsTrait.php`) |

## What It Does

Creates a PHP trait file from a stub template with the StudlyCase name applied. Use this to extract reusable Eloquent behaviour (scopes, accessors, relationships) into a standalone trait that can be mixed into any model.

## Examples

```bash
php artisan modularous:make:model:trait HasAnalytics
php artisan mod:c:model:trait HasPricing
```

## Output

Generates the trait file at:

```
Modules/{Module}/Traits/{StudlyName}Trait.php
```

## Related

- [create:repository-trait](./create-repository-trait) — same pattern for repository traits
- [Entity Traits reference](/system-reference/backend/entity-traits/overview) — built-in model traits shipped with Modularous
