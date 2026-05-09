---
sidebarPos: 7
sidebarTitle: Urls
---

# Urls

**Class**: `Unusualify\Modularous\Http\ViewComposers\Urls`  
**Source**: `src/Http/ViewComposers/Urls.php`

Injects a `$urls` array of commonly used admin route URLs into every view. Centralises route URL generation so frontend components can reference named routes without hardcoding paths or calling `route()` in multiple places.

## Injected Variable

| Variable | Type | Description |
|----------|------|-------------|
| `urls` | `array` | Map of named URL keys to their resolved admin route URLs |

## URL Map

| Key | Route | Description |
|-----|-------|-------------|
| `profileShow` | `admin.profile.show` | URL to the current user's profile view page |
| `profileUpdate` | `admin.profile.update` | URL to the profile update endpoint (PUT/PATCH) |

## Usage in Views

```blade
{{-- Blade --}}
<a href="{{ $urls['profileShow'] }}">My Profile</a>
```

```js
// Inertia (shared props)
const { urls } = usePage().props

// Use directly in a link or form action
router.put(urls.profileUpdate, formData)
```

## Adding More URLs

The `Urls` composer is intentionally minimal. To add more globally available URLs, extend the composer or create a new one and register it in your application's service provider:

```php
// In AppServiceProvider::boot()
view()->composer('*', function ($view) {
    $view->with('urls', array_merge($view->getData()['urls'] ?? [], [
        'dashboard' => route('admin.dashboard'),
    ]));
});
```
