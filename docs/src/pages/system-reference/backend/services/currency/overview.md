---
sidebarPos: 3
sidebarTitle: Overview
sidebarGroupTitle: Currency
outline: deep
---

# Currency Providers

**Directory**: `src/Services/Currency/`
**Contract**: `Unusualify\Modularous\Contracts\CurrencyProviderInterface`

The Currency namespace provides interchangeable implementations of `CurrencyProviderInterface` — the abstraction used when Modularous needs to resolve currency records from the database (e.g. for price conversion, multi-currency selects, and localisation). The active provider is resolved from the service container and swapped depending on which modules are installed.

Together with [`CurrencyExchangeService`](/system-reference/backend/services/currency-exchange-service) (live exchange-rate fetching) and the [`CurrencyExchange`](/system-reference/backend/facades/currency-exchange) facade, these pieces cover everything currency-related in Modularous:

| Piece | Responsibility |
|-------|---------------|
| `CurrencyProviderInterface` + implementations | **Data access** — look up currency records stored in the database |
| `CurrencyExchangeService` | **Rate fetching** — hit an external API, cache rates, convert amounts |
| `CurrencyExchange` facade | Shorthand for the exchange service |
| `CurrencyExchangeController` | HTTP endpoints that expose the exchange service |

## In This Section

| Page | Purpose |
|------|---------|
| **Overview** (this page) | When each provider is used, configuration, binding |
| [NullCurrencyProvider](./null-currency-provider) | Default fallback — safe empty returns |
| [SystemPricingCurrencyProvider](./system-pricing-currency-provider) | Reads from the `SystemPricing` module's `Currency` entity |
| [Usage Patterns](./usage-patterns) | Common ways to call the provider from controllers, repositories, and views |
| [Custom Provider](./custom-provider) | Step-by-step guide to implementing your own provider |

---

## How Provider Selection Works

```
                 ┌─────────────────────────┐
CurrencyProvider │ CurrencyProviderInterface │
                 └───────────┬─────────────┘
                             │ resolve()
                  ┌──────────┴──────────┐
                  │                     │
         SystemPricing enabled?         no pricing module
                  │                     │
                  ▼                     ▼
     SystemPricingCurrencyProvider   NullCurrencyProvider
```

1. A module provides its own binding in a service provider (e.g. `SystemPricing` binds `SystemPricingCurrencyProvider`).
2. If no module provides one, `BaseServiceProvider` falls back to `NullCurrencyProvider`.
3. Downstream code **always** resolves the interface — it never references a concrete class:

```php
$provider = app(CurrencyProviderInterface::class);
```

## CurrencyProviderInterface

```php
interface CurrencyProviderInterface
{
    public function findByIso4217(string $isoCode): ?object;
    public function getCurrenciesForSelect(): array;
    public function isAvailable(): bool;
}
```

| Method | Contract |
|--------|----------|
| `findByIso4217(string $isoCode): ?object` | Return the currency record whose ISO 4217 code matches (case-insensitive), or `null` when it does not exist / the provider is not available. |
| `getCurrenciesForSelect(): array` | Return `[['id' => int, 'name' => string, 'iso' => string], ...]` — the shape consumed by Modularous select inputs. Return `[]` when the provider is not available. |
| `isAvailable(): bool` | `true` when the provider can return real data, `false` when it is the null fallback. Call this **before** relying on return values. |

::: tip Why `?object`?
The interface deliberately returns `?object` rather than a concrete `Currency` model class. Implementations may back the data with different Eloquent models (or any value object) — the interface simply guarantees the three methods.
:::

## Configuration

```php
// config/modularous.php (merged from packages/modularous/config/merges/services.php)
'services' => [
    'currency_exchange' => [
        'active'        => env('CURRENCY_EXCHANGE_ACTIVE', true),
        'api_key'       => env('CURRENCY_EXCHANGE_API_KEY'),
        'base_currency' => 'EUR',
        'endpoint'      => 'https://api.freecurrencyapi.com/v1/latest',
        'parameters'    => [
            'apiKey'        => 'apikey',
            'baseCurrency'  => 'base_currency',
        ],
        'rates_key'     => 'data',
    ],
],
```

### Effect of `services.currency_exchange.active`

| `active` | Behaviour |
|----------|-----------|
| `true` (default) | `getCurrenciesForSelect()` returns **only the base currency**. Forms expose one currency choice and the repository auto-converts to the others using `CurrencyExchangeService`. |
| `false` | `getCurrenciesForSelect()` returns **every currency**. Forms let the operator pick any currency directly; no auto-conversion. |

This is the key toggle that controls whether the system is effectively single-currency-with-conversion or multi-currency-manual.

## Binding a Custom Provider

Bind from a service provider **before** `BaseServiceProvider::register()` runs — or use `$this->app->extend()` to replace the already-bound instance:

```php
// AppServiceProvider::register()
$this->app->bind(
    \Unusualify\Modularous\Contracts\CurrencyProviderInterface::class,
    \App\Services\MyCurrencyProvider::class,
);
```

For a walkthrough with a working example, see [Custom Provider](./custom-provider).

## Quick Reference

| I want to… | Use |
|------------|-----|
| Look up a currency by ISO code | `app(CurrencyProviderInterface::class)->findByIso4217('USD')` |
| Populate a currency `<select>` | `app(CurrencyProviderInterface::class)->getCurrenciesForSelect()` |
| Check whether currencies are available before rendering | `$provider->isAvailable()` |
| Convert an amount at live rates | `CurrencyExchange::convertTo($amount, 'USD')` |
| Fetch the raw rates table (cached 1h) | `CurrencyExchange::fetchExchangeRates()` |

## See Also

- [CurrencyExchangeService](/system-reference/backend/services/currency-exchange-service) — live rate fetcher
- [CurrencyExchange facade](/system-reference/backend/facades/currency-exchange) — shorthand access
- [CurrencyExchangeController](/system-reference/backend/http/controllers/currency-exchange-controller) — HTTP endpoints
- [PricesTrait](/system-reference/backend/repository-traits/payment) — repository trait that drives multi-currency persistence
