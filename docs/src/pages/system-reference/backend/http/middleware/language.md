---
sidebarPos: 8
sidebarTitle: LanguageMiddleware
---

# LanguageMiddleware

**File**: `src/Http/Middleware/LanguageMiddleware.php`  
**Alias**: `modularity.language`  
**Part of**: `modularity.core` group

Resolves and applies the active locale, timezone, and currency for the current request. Runs on every route that uses the `modularity.core` middleware group.

## Locale Resolution Priority

The locale is determined in this order:

1. **`?language=` query parameter** — explicit override, takes highest priority.
2. **Authenticated user's `language` property** — `$request->user()->language`.
3. **GeoIP auto-detection** — only when `MODULARITY_AUTO_LOCALE_FINDER=true` in `.env`; uses `geoip()->getLocation($ip)->iso_code`. The resolved code must be in `modularity.available_user_locales` or it is ignored.
4. **App default locale** — `app()->getLocale()` fallback.

> **Translation route exception**: When the current route is `languages.translations.index`, the locale is forced back to the fallback locale regardless of the resolved value.

## What Gets Set

| Config / setting | Value |
|-----------------|-------|
| `modularity.locale` | Resolved locale code |
| `modularity.timezone` | `auth()->user()->timezone` or `'Europe/London'` |
| `app.locale` | Set via `App::setLocale()` |
| `app.fallback_locale` | Set via `App::setFallbackLocale()` |
| Carbon locale | `CarbonInterval::setLocale()` + `Carbon::setLocale()` |

## Currency Resolution

After the locale is set, the middleware determines the active currency:

1. If `services.currency_exchange.active` is **false** (single-currency mode): looks up `payment.locale_currencies.{locale}` in config; falls back to `priceable.currency`.
2. If the resolved currency differs from the current `priceable.currency`, updates `config(['priceable.currency' => $currency])` and calls `CurrencyProviderInterface::findByIso4217()` to attach the currency model to the request.

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `MODULARITY_AUTO_LOCALE_FINDER` | `false` | Enable GeoIP-based locale detection |

## Configuration

```php
// config/modularity.php
'available_user_locales' => ['en', 'tr', 'de'],
'fallback_locale'        => 'en',

'payment' => [
    'locale_currencies' => [
        'tr' => 'TRY',
        'de' => 'EUR',
    ],
],
```
