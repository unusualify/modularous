---
sidebarPos: 24
sidebarTitle: Make Request
---

# Make Request

> Generate a Form Request class (store/update validation) for a module.

## Command Information

- **Signature:** `modularous:make:request {module} {request} [--rules=]`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | Yes | The module the request belongs to (e.g. `Blog`) |
| `request` | Yes | Request class name (e.g. `StorePost` → generates `StorePostRequest`) |

## Options

| Option | Description |
|--------|-------------|
| `--rules=` | Seed the request with validation rules in `field=rule1\|rule2&field2=rule` format (parsed by `ValidatorParser`) |

## Examples

```bash
# Generate an empty StorePostRequest
php artisan modularous:make:request Blog StorePost

# Generate with seeded validation rules
php artisan modularous:make:request Blog StorePost --rules="title=required|string|max:255&body=nullable|string"
```

## Output

```
Modules/Blog/Http/Requests/StorePostRequest.php
```

## Related

- [make:controller](./make-controller) — the controller that uses the request
- [Decomposers\ValidatorParser](/system-reference/backend/support/decomposers#validatorparser) — parses the `--rules` string
