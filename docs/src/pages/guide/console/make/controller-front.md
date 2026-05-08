---
sidebarPos: 9
sidebarTitle: make:controller:front
---

# make:controller:front

> Create a front-end (public-facing) controller for a module

**Signature**: `modularity:make:controller:front`

**Category**: Make

---

## Description

Generates a front-end controller in the module's `Http/Controllers/Front/` path. Suitable for public-facing pages, Inertia.js views, or SSR routes. The stub receives the module name, studly/lower variants, and route name.

---

## Usage

```
modularity:make:controller:front <module> <name>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | yes | Target module |
| `name` | yes | Controller class name |

---

## Examples

```bash
php artisan modularity:make:controller:front Blog Post
# → Blog/Http/Controllers/Front/PostController.php
```

---

## Output

`{Module}/Http/Controllers/Front/{Name}Controller.php`

**Stub**: `route-controller-front.stub`

---

## See also

- [make:controller](./controller) — admin-panel variant
- [make:controller:api](./controller-api) — API variant
- [System Reference](/system-reference/backend/console/make#makecontrollerfrontcommand)
