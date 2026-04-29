---
sidebarPos: 5
sidebarTitle: Localization
---

# Localization

**Class**: `Unusualify\Modularity\Http\ViewComposers\Localization`  
**Source**: `src/Http/ViewComposers/Localization.php`

Injects the full Modularous locale and language configuration into every view as `$modularityLocalization`. The frontend i18n layer reads this object to initialise locale settings, available languages, and translation strings without a separate API call.

## Injected Variable

| Variable | Type | Description |
|----------|------|-------------|
| `modularityLocalization` | `array` | Full locale configuration returned by `get_modularity_localization_config()` |

## Data Shape

The shape of `$modularityLocalization` is determined by the `get_modularity_localization_config()` helper, which typically returns:

| Key | Description |
|-----|-------------|
| `locale` | Current application locale (e.g. `en`) |
| `fallback_locale` | Fallback locale when a translation is missing |
| `locales` | All enabled locales as `[{value, label}]` pairs |
| `translations` | Flat key→value translation strings for the frontend |

## Usage in Views

```blade
{{-- Available as a Blade variable --}}
@json($modularityLocalization)
```

```js
// Inertia (shared props)
const { modularityLocalization } = usePage().props

const { locale, locales, translations } = modularityLocalization
```

## Configuration

Locale settings are controlled via the Modularous config and Laravel's `app.locale`:

```php
// config/modularity.php
'locales' => [
    ['value' => 'en', 'label' => 'English'],
    ['value' => 'tr', 'label' => 'Türkçe'],
],
```
