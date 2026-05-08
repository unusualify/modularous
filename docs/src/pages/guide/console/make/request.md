---
sidebarPos: 10
sidebarTitle: make:request
---

# make:request

> Create a Form Request class for a module

**Signature**: `modularity:make:request`

**Category**: Make

---

## Description

Generates a Form Request extending the configured `base_request`. The `--rules` string is parsed by `ValidatorParser` and inlined as a PHP array into the generated class.

---

## Usage

```
modularity:make:request [options] <module> <request>
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | yes | Target module |
| `request` | yes | Request class name (suffix `Request` added automatically) |

### Options

| Option | Description |
|--------|-------------|
| `--rules=` | Validation rules string (e.g. `name:required\|string,email:required\|email`) |

---

## Examples

### Basic request

```bash
php artisan modularity:make:request Blog Post
# → Blog/Http/Requests/PostRequest.php
```

### Request with inline rules

```bash
php artisan modularity:make:request Blog Post \
    --rules="title:required|string|max:255,body:required|string,published_at:nullable|date"
```

---

## Output

`{Module}/Http/Requests/{Request}Request.php`

**Stub**: `route-request.stub`

---

## See also

- [make:route](./route) — generates the request as part of a full scaffold
- [System Reference](/system-reference/backend/console/make#makerequestcommand)
