---
sidebarPos: 5
sidebarTitle: Localization
---

# Localization

**Class**: `Unusualify\Modularous\Http\ViewComposers\Localization`  
**Source**: `src/Http/ViewComposers/Localization.php`

Injects the full Modularous locale and language configuration into every view as `$modularousLocalization`. The frontend i18n layer reads this object to initialise locale settings, available languages, and translation strings without a separate API call.

## Injected Variable

| Variable | Type | Description |
|----------|------|-------------|
| `modularousLocalization` | `array` | Full locale configuration returned by `get_modularous_localization_config()` |

## Data Shape

The shape of `$modularousLocalization` is determined by the `get_modularous_localization_config()` helper, which typically returns:

| Key | Description |
|-----|-------------|
| `locale` | Current application locale (e.g. `en`) |
| `fallback_locale` | Fallback locale when a translation is missing |
| `locales` | All enabled locales as `[{value, label}]` pairs |
| `translations` | Flat key→value translation strings for the frontend |

## Usage in Views

```blade
{{-- Available as a Blade variable --}}
@json($modularousLocalization)
```

```js
// Inertia (shared props)
const { modularousLocalization } = usePage().props

const { locale, locales, translations } = modularousLocalization
```

## Configuration

Locale settings are controlled via the Modularous config and Laravel's `app.locale`:

```php
// config/modularous.php
'locales' => [
    ['value' => 'en', 'label' => 'English'],
    ['value' => 'tr', 'label' => 'Türkçe'],
],
```
