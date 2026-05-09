---
sidebarPos: 3
sidebarTitle: Create Feature
---

# Create Feature

> Interactively scaffold a cross-cutting feature — optionally generating a model trait, a repository trait, or both.

## Command Information

- **Signature:** `modularous:make:feature [name?]`
- **Aliases:** `modularous:create:feature`, `mod:c:feature`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | No | Feature name. If omitted, you are prompted interactively |

## What It Does

Asks (via interactive prompts) whether to create:
- A **repository trait** — delegates to `modularous:make:repository:trait`
- A **model trait** — delegates to `modularous:make:model:trait`

Both are generated with the same StudlyCase name. Use this when a new feature requires behaviour spread across the model and repository layers.

## Examples

```bash
# Interactive — prompts for name and which traits to create
php artisan modularous:make:feature

# Provide the name upfront
php artisan modularous:make:feature HasAnalytics
```

## Related

- [create:model-trait](./create-model-trait)
- [create:repository-trait](./create-repository-trait)
