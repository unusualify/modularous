---
sidebarPos: 13
sidebarTitle: CurrencyExchangeService
---

# CurrencyExchangeService

**File**: `src/Services/CurrencyExchangeService.php`  
**Facade**: `Unusualify\Modularous\Facades\CurrencyExchange`

Fetches live currency exchange rates from a configurable external API, caches the result for one hour, and provides amount conversion helpers.

## Configuration

All options live under `config/modularous.php` → `services.currency_exchange`:

```php
'services' => [
    'currency_exchange' => [
        'endpoint'      => 'https://api.freecurrencyapi.com/v1/latest',
        'api_key'       => env('CURRENCY_EXCHANGE_API_KEY'),
        'base_currency' => 'EUR',

        // Map of service property names → query parameter names
        'parameters' => [
            'apiKey'       => 'apikey',
            'baseCurrency' => 'base_currency',
        ],

        // JSON key in the API response that holds the rates object
        'rates_key' => 'data',
    ],
],
```

## Key Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `fetchExchangeRates` | `fetchExchangeRates(): array` | Fetch rates from the API and cache for 1 hour. Returns `[currency => rate]`. Throws on API failure. |
| `convertTo` | `convertTo(float $amount, string $currency, int $decimals, string $round): float` | Convert `$amount` from the base currency to `$currency`. |
| `getExchangeRate` | `getExchangeRate(string $currency, int $decimals, string $round): float` | Return the exchange rate for a single currency. |

## Rounding

The `$round` parameter on `convertTo()` and `getExchangeRate()` controls how the result is rounded:

| Value | Behaviour |
|-------|-----------|
| `'round'` (default) | `round($value, $decimals)` |
| `'ceil'` | `ceil($value)` |
| `'floor'` | `floor($value)` |

## Cache

Rates are cached using the key `exchange_rates` for **1 hour** via `Cache::remember`. To force a refresh, clear this key:

```bash
php artisan cache:forget exchange_rates
```

## Facade Usage

```php
use Unusualify\Modularous\Facades\CurrencyExchange;

// Convert 100 EUR to USD
$usd = CurrencyExchange::convertTo(100, 'USD');

// Get the raw rate for TRY
$rate = CurrencyExchange::getExchangeRate('TRY', decimals: 4);

// Convert, ceiling the result
$price = CurrencyExchange::convertTo($euroPrice, 'GBP', decimals: 2, round: 'ceil');
```

## Currency Providers

The `CurrencyExchangeService` is supplemented by currency provider classes used in the pricing module:

| Class | Description |
|-------|-------------|
| `Currency/SystemPricingCurrencyProvider` | Fetches the configured currency from the system pricing module |
| `Currency/NullCurrencyProvider` | No-op provider returned when no pricing module is active |

The active provider is resolved via `modularous.currency_provider` config key.
