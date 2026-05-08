---
sidebarPos: 6
sidebarTitle: OauthRequest
---

# OauthRequest

**File**: `src/Http/Requests/OauthRequest.php`
**Namespace**: `Unusualify\Modularity\Http\Requests`
**Extends**: [`Request`](./request)

Validates the OAuth provider portion of the authentication callback flow. The provider value is accepted from either the request body or the route parameter and must match one of the configured providers.

## Route parameter merging

`all()` is overridden so the `provider` route segment is surfaced as a normal request input:

```php
public function all($keys = null)
{
    $data = parent::all();
    $data['provider'] = $this->input('provider', $this->route('provider'));

    return $data;
}
```

This lets downstream validation and controllers read `$request->input('provider')` regardless of whether the value came in via the URL or the posted body.

## Rules

```php
[
    'provider' => ['required', Rule::in(array_keys(modularityConfig('oauth.providers', [])))],
]
```

The whitelist is read dynamically from `modularity.oauth.providers`, so enabling a new provider in config immediately makes it accepted here — no code change required.

## Redirect on validation failure

`getRedirectUrl()` attempts to return the route for the authenticated callback:

```php
$url->route(config('modularity.admin_route_name_prefix') . '.loginHandleCallbackProvider', ['provider' => $provider]);
```

> [!WARNING]
> The `$provider` variable in `getRedirectUrl()` is not defined in the current source — the method will throw if validation fails and a redirect target is needed. This path is exercised only on failure, so the happy path is unaffected.

## Related

- [`LoginController`](/system-reference/backend/http/controllers/auth/login-controller) — consumes this request during callback handling
- `config/modularity.php` → `oauth.providers` — provider whitelist driving the `Rule::in` list
