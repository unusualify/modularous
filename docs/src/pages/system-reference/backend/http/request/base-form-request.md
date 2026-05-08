---
sidebarPos: 2
sidebarTitle: BaseFormRequest
---

# BaseFormRequest

**File**: `src/Http/Requests/BaseFormRequest.php`
**Namespace**: `Unusualify\Modularity\Http\Requests`
**Extends**: `Illuminate\Foundation\Http\FormRequest`

A thin convenience base for writing form requests where validation differs by HTTP method. Instead of overriding `rules()` with a branching `switch`, subclasses implement one small method per verb.

## Method dispatch

`rules()` inspects `$this->method()` and calls one of:

| HTTP method | Called method |
|-------------|---------------|
| `POST` | `store()` |
| `PUT`, `PATCH` | `update()` |
| `DELETE` | `destroy()` |
| any other (`GET`, `HEAD`, …) | `view()` |

Each of these returns an array of rules. The default implementations return empty arrays, so you only override the verbs you care about.

```php
class StorePostRequest extends BaseFormRequest
{
    public function store(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body'  => 'required|string',
        ];
    }

    public function update(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'body'  => 'sometimes|string',
        ];
    }
}
```

## Authorization

`authorize()` returns `true` by default. Override it if the request should be guarded.

## Failed-validation response

`failedValidation()` branches on `$this->wantsJson()`:

| Client | Response |
|--------|----------|
| JSON (e.g. Inertia / API) | `400 JSON` with `{ "status": 400, "errors": <bag> }` |
| HTML | `redirect()->back()` with flash message `"Ops! Some errors occurred"` and validator errors |

In both cases a `ValidationException` is thrown with the correct error bag and redirect URL, matching Laravel's normal control-flow expectations.

## When to extend it

Use `BaseFormRequest` when your rules depend on verb but not on a specific model. If you need translation-aware rules or schema merging against a model's `getTranslatedAttributes()`, extend the model-aware [`Request`](./request) class instead.

## Known subclasses in this package

| Subclass | Purpose |
|----------|---------|
| [`MediaRequest`](./media-request) | Media-library uploads (authorization-only; rules disabled) |
