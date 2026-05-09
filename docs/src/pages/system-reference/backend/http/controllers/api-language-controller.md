---
sidebarPos: 2
sidebarTitle: API\LanguageController
---

# API\LanguageController

**File**: `src/Http/Controllers/API/LanguageController.php`  
**Namespace**: `Unusualify\Modularous\Http\Controllers\API`  
**Extends**: `Illuminate\Routing\Controller`

Serves all translation strings as a JSON payload for frontend consumption. Results are cached to a file store for 10 minutes to avoid recomputing translations on every request.

## Constructor

```php
public function __construct(Translation $translation)
```

Injects the `JoeDixon\Translation\Drivers\Translation` driver used to load translation files.

## Methods

### `index(Request $request): JsonResponse`

Returns a JSON object containing all application translations, keyed by locale and translation group.

**Caching behaviour**:

| Detail | Value |
|--------|-------|
| Cache store | `file` |
| Cache key | `modularous-languages` |
| TTL | 600 seconds (10 minutes) |

Translations are resolved via `app('translator')->getTranslations()` which aggregates group and single-line translation files across all registered modules and the base application.

### `store()`, `show($id)`, `update($id)`, `destroy($id)`

Stub methods — not implemented. The controller is read-only; translation management is handled through translation files or a dedicated translation UI.

## Usage

The frontend fetches translations on initial page load via this endpoint and stores them in the Vue i18n instance, enabling `$t('key')` calls throughout the admin panel and public views.

## Related

- `ManageTranslations` trait on `BaseController` — provides server-side translation fallback chain for module-specific keys
