---
sidebarPos: 2
sidebarTitle: SystemPricingCurrencyProvider
---

# SystemPricingCurrencyProvider

**File**: `src/Services/Currency/SystemPricingCurrencyProvider.php`  
**Implements**: `CurrencyProviderInterface`  
**Requires**: `Modules\SystemPricing\Entities\Currency` (SystemPricing module)

`SystemPricingCurrencyProvider` reads currency data from the `SystemPricing` module's `Currency` entity. All database queries are cached for 1 hour using Laravel's default cache store.

## Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `findByIso4217(string $isoCode): ?object` | `Currency` model or `null` | Looks up a currency by ISO 4217 code (e.g. `'USD'`). Result cached as `currency_by_iso_4217_{code}`. |
| `findById(int $id): ?object` | `Currency` model or `null` | Looks up a currency by primary key. Result cached as `currency_by_id_{id}`. |
| `getCurrenciesForSelect(): array` | `[['id', 'name', 'iso'], ...]` | Returns all currencies as a flat array for select inputs. When `services.currency_exchange.active` is `true`, only returns currencies matching the configured base currency. |
| `isAvailable(): bool` | `true` when `Currency` class exists | Returns `false` if the `SystemPricing` module is not installed or the `Currency` entity cannot be resolved. |

## Cache Keys

| Key | TTL | Content |
|-----|-----|---------|
| `currency_by_iso_4217_{code}` | 1 hour | Single `Currency` model |
| `currency_by_id_{id}` | 1 hour | Single `Currency` model |

## Configuration

```php
// config/modularous.php
'services' => [
    'currency_exchange' => [
        'active'        => true,
        'base_currency' => 'EUR',
    ],
],
```

When `active` is `true`, `getCurrenciesForSelect()` filters to only return the base currency, limiting conversion targets to one.

## Notes

- Each method checks `class_exists(Currency::class)` before querying. If the `SystemPricing` module is disabled mid-request, the methods return `null` / `[]` without throwing.
- Implement a custom provider and bind it to `CurrencyProviderInterface` in a service provider to replace this with a different data source.
