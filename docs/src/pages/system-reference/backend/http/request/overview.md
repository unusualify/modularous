---
sidebarPos: 1
sidebarTitle: Overview
---

# Requests

**Directory**: `src/Http/Requests/`
**Namespace**: `Unusualify\Modularity\Http\Requests`

Modularous ships seven `FormRequest` classes. Two of them — `BaseFormRequest` and `Request` — are base classes that the rest of the package (and consumers) extend from; the remaining five are concrete requests used by specific controllers.

## Class hierarchy

```
Illuminate\Foundation\Http\FormRequest
│
├── BaseFormRequest            ← method-dispatch base (store/update/view/destroy)
│   └── MediaRequest           ← media library uploads
│
├── Request (abstract)         ← model-aware base with translation + schema merging
│   ├── FileRequest            ← file library uploads (varies by endpoint)
│   └── OauthRequest           ← OAuth callback validation
│
├── StorePermissionRequest     ← direct FormRequest subclass
└── StoreRoleRequest           ← direct FormRequest subclass
```

## Classes

| Class | Base | Purpose | Page |
|-------|------|---------|------|
| `BaseFormRequest` | `FormRequest` | Dispatches validation to `view()` / `store()` / `update()` / `destroy()` based on HTTP method; custom `failedValidation()` with JSON/redirect branching | [BaseFormRequest →](./base-form-request) |
| `Request` *(abstract)* | `FormRequest` | Model-aware base that merges schema rules with translated-attribute rules across locales; injects `unique_table` / `unique_translation` helpers | [Request →](./request) |
| `FileRequest` | `Request` | Validates file-library upload payloads; rules depend on the configured `file_library.endpoint_type` (local / azure / s3) | [FileRequest →](./file-request) |
| `MediaRequest` | `BaseFormRequest` | Thin authorization pass-through for media-library requests (validation currently disabled) | [MediaRequest →](./media-request) |
| `OauthRequest` | `Request` | Validates the OAuth `provider` against `modularity.oauth.providers`; merges route param into `all()` | [OauthRequest →](./oauth-request) |
| `StorePermissionRequest` | `FormRequest` | Validates permission creation (`name` required, unique on `permissions`) | [StorePermissionRequest →](./store-permission-request) |
| `StoreRoleRequest` | `FormRequest` | Validates role creation (`name` required, unique on `roles`, min 4 chars) | [StoreRoleRequest →](./store-role-request) |

## Extension points

| You want to... | Extend |
|----------------|--------|
| Write a simple request with method-based rules (create/update/delete) | `BaseFormRequest` |
| Write a model-aware request that handles translations automatically | `Request` |
| Validate a permission/role-like plain payload | Laravel's `FormRequest` directly (as `StorePermissionRequest` does) |

## Related

- [Controllers](/system-reference/backend/http/controllers/overview) — controllers that consume these requests
- [Entity Traits · IsTranslatable](/system-reference/backend/entity-traits/translation/is-translatable) — translation flag that drives `Request::mergeRules()`
