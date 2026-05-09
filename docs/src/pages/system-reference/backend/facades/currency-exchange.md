---
sidebarPos: 3
sidebarTitle: CurrencyExchange
---

# CurrencyExchange

**Facade**: `Unusualify\Modularous\Facades\CurrencyExchange`  
**Accessor**: `currency.exchange`  
**Underlying**: `Unusualify\Modularous\Services\CurrencyExchangeService`

Fetches live exchange rates from an external provider and converts amounts between currencies. See [CurrencyExchangeService](/system-reference/backend/services/currency-exchange-service) for implementation details.

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `fetchExchangeRates` | `(): array` | Fetches and returns current exchange rates from the configured provider |
| `convertTo` | `(float $amount, string $targetCurrency): float` | Converts `$amount` from the base currency to `$targetCurrency` |
| `getExchangeRate` | `(string $currency): float` | Returns the exchange rate for a single currency code |

## Usage

```php
use Unusualify\Modularous\Facades\CurrencyExchange;

$rate = CurrencyExchange::getExchangeRate('EUR');

$amountInEur = CurrencyExchange::convertTo(100.00, 'EUR');
```

## Notes

- Exchange rates are cached to avoid repeated external HTTP requests.
- The base currency is configured via `modularous.currency.base` (default: `USD`).
