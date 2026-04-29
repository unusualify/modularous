---
sidebarPos: 4
sidebarTitle: Create Input Hydrate
---

# Create Input Hydrate

> Generate a Hydrate class that defines the input schema for a module's form fields.

## Command Information

- **Signature:** `modularity:make:input:hydrate <name>`
- **Aliases:** `modularity:create:input:hydrate`, `mod:c:input:hydrate`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | Yes | Name of the Hydrate class (e.g. `Product` → generates `ProductHydrate`) |

## What It Does

Creates `{StudlyName}Hydrate.php` from the `input-hydrate.stub` template. The generated class defines the input schema — field types, labels, validation rules, and connector sources — used by `FormBase` to render the module's create/edit form.

## Examples

```bash
# Generate ProductHydrate
php artisan modularity:make:input:hydrate Product

# Short alias
php artisan mod:c:input:hydrate Product
```

## Output

Creates the Hydrate class at the path configured for the target module, typically:

```
Modules/{Module}/Http/Controllers/Hydrates/{Name}Hydrate.php
```

## Related

- [make:module](/guide/console/generators/make-module) — generates the Hydrate as part of a full module scaffold
- [Hydrates reference](/system-reference/hydrates) — full schema contract for Hydrate classes
