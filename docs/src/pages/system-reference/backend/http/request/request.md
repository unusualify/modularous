---
sidebarPos: 3
sidebarTitle: Request
---

# Request (abstract)

**File**: `src/Http/Requests/Request.php`
**Namespace**: `Unusualify\Modularity\Http\Requests`
**Extends**: `Illuminate\Foundation\Http\FormRequest`
**Uses**: `Unusualify\Modularity\Traits\ManageTraits`

An abstract model-aware form request that knows how to merge validation rules across translated attributes and inject model-scoped helpers. Intended as the base class for any request that validates payloads destined for a Modularous Eloquent model.

## Construction

```php
public function __construct(protected $rules = [])
{
    $this->model = $this->model();
}
```

- `$rules` — optional default schema passed at construction (used by `mergeSchemaRules()`).
- `model()` comes from `ManageTraits` and resolves the model class the request is for.

## Rule dispatch

Unlike [`BaseFormRequest`](./base-form-request), this class branches only on `POST` / `PUT`:

| HTTP method | Rules pipeline |
|-------------|----------------|
| `POST` | `mergeRules( rulesForAll() + rulesForCreate() )` |
| `PUT` | `mergeRules( rulesForAll() + rulesForUpdate() )` |
| other | `[]` |

Subclasses implement `rulesForAll()`, `rulesForCreate()`, and `rulesForUpdate()` to describe their schema.

## Translation-aware rule merging

`mergeRules()` performs three steps:

1. **`mergeSchemaRules($rules)`** — placeholder hook for combining constructor-provided `$this->rules` with method rules. Currently passes through unchanged.
2. **Split translated vs. non-translated attributes** using the model's `getTranslatedAttributes()` (only if the model uses the [`IsTranslatable`](/system-reference/backend/entity-traits/translation/is-translatable) trait).
3. **Expand translated rules per locale** by calling `updateRules()` once for each locale in `getLocales()`, producing keys like `title.en`, `title.tr`, etc.

The result is then run through [`hydrateRules()`](#rule-hydration) to expand helper tokens.

## Per-locale rule expansion

For every translated field, `updateRules()`:

- Creates per-locale keys (`field.en`, `field.tr`, …).
- Drops `required*` rules and replaces them with `nullable` when the locale is not active (`$localeActive === false`).
- Rewrites `required_*` rules that reference peer fields so they point to the locale-specific key (`required_with:name` → `required_with:name.en`).
- Translates `unique_translation` rules into a closure that queries the translation model, excluding the current record by its `translationRelationKey` if `id` is present.

## Rule hydration

`hydrateRules()` scans string rules for the token `unique_table` and rewrites it as:

```text
unique:<model-table>,<field>[,<id>]
```

The `id` suffix is appended automatically when `$this->id` is set, making the rule safe to use for both store and update flows.

Example — a subclass can write:

```php
public function rulesForAll(): array
{
    return [
        'email' => 'required|email|unique_table',
    ];
}
```

and on update it expands to `unique:users,email,42`.

## Translated message helper

`messagesForTranslatedFields($messages, $fields)` and its private helper `updateMessages()` mirror the rule expansion for error messages — they fan each defined message out across every locale, replacing `{lang}` with the actual locale code.

## Authorization

`authorize()` returns `true`. Override in a subclass if the action should be guarded.

## Known subclasses in this package

| Subclass | Purpose |
|----------|---------|
| [`FileRequest`](./file-request) | File-library upload payloads keyed by endpoint type |
| [`OauthRequest`](./oauth-request) | OAuth provider validation on callback |

## Related

- [`ManageTraits`](#) — resolves `$this->model()` (internal plumbing trait)
- [`IsTranslatable`](/system-reference/backend/entity-traits/translation/is-translatable) — the flag that activates per-locale rule expansion
- [`HasTranslation`](/system-reference/backend/entity-traits/translation/has-translation) — provides `getTranslationModelName()` and `getTranslationRelationKey()` used by the closure-based `unique_translation` rule
