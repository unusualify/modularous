---
sidebarPos: 8
sidebarTitle: CurrencyExchangeController
---

# CurrencyExchangeController

**File**: `src/Http/Controllers/CurrencyExchangeController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers`  
**Extends**: `Illuminate\Routing\Controller`

Manages currency exchange rates and performs currency conversions. Delegates all business logic to the injected `CurrencyExchangeService`.

## Constructor

```php
public function __construct(CurrencyExchangeService $service)
```

## Methods

### `fetchRates(): JsonResponse`

Fetches the latest exchange rates from the configured provider and persists them to the database. Typically called via a scheduled job or an admin-triggered action.

### `convert(Request $request): JsonResponse`

Converts an amount from the base currency to a target currency.

**Validation**:

| Field | Rules | Description |
|-------|-------|-------------|
| `amount` | required, numeric | Amount to convert |
| `currency` | required, size:3 | ISO 4217 target currency code |

Returns the converted amount and the applied exchange rate.

### `getRate(Request $request, string $currency): JsonResponse`

Returns the current exchange rate for a specific currency.

**Route parameter**:

| Parameter | Rules | Description |
|-----------|-------|-------------|
| `$currency` | size:3 | ISO 4217 currency code |

## Related

- `CurrencyExchangeService` — underlying service that communicates with rate providers
