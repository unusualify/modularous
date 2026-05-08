---
sidebarPos: 3
sidebarTitle: Usage Patterns
outline: deep
---

# Currency Usage Patterns

Practical patterns for consuming `CurrencyProviderInterface` and the `CurrencyExchange` facade from controllers, repositories, Blade/Vue views, and tests. Each pattern pairs the **shortest working snippet** with notes on pitfalls.

## 1. Feature-flag UI on availability

Always check `isAvailable()` before rendering a currency picker or price-converting UI. When the SystemPricing module is not installed, the fallback provider returns empty data — you want the UI to degrade gracefully instead of showing an empty `<select>`.

```php
use Unusualify\Modularity\Contracts\CurrencyProviderInterface;

$provider = app(CurrencyProviderInterface::class);

if (! $provider->isAvailable()) {
    return view('admin.dashboard', ['currencies' => null]);
}

return view('admin.dashboard', [
    'currencies' => $provider->getCurrenciesForSelect(),
]);
```

In Blade:

```blade
@if ($currencies !== null)
    <select name="currency">
        @foreach ($currencies as $currency)
            <option value="{{ $currency['iso'] }}">{{ $currency['name'] }}</option>
        @endforeach
    </select>
@else
    <p class="text-muted">Currency selection requires the SystemPricing module.</p>
@endif
```

## 2. Resolve a currency by ISO code

```php
$currency = app(CurrencyProviderInterface::class)->findByIso4217('USD');

if (! $currency) {
    throw new \RuntimeException('USD is not configured');
}

$id     = $currency->id;
$name   = $currency->name ?? $currency->symbol ?? 'USD';
```

Result is **cached for 1 hour** by `SystemPricingCurrencyProvider`. To bust the cache after editing a currency, call `Cache::forget("currency_by_iso_4217_USD")` or clear the `modularity` cache (`php artisan modularity:cache:clear`).

## 3. Populate a form select

The `getCurrenciesForSelect()` shape matches what Modularous' schema-driven form inputs expect for `options`:

```php
// In a route config or form builder
[
    'name'    => 'currency_id',
    'type'    => 'select',
    'options' => app(CurrencyProviderInterface::class)->getCurrenciesForSelect(),
    'item_value' => 'id',
    'item_title' => 'name',
],
```

When `services.currency_exchange.active` is `true`, the select contains **only the base currency** — that's by design. The price repository will then auto-convert to all enabled currencies on save.

## 4. Convert at display time (live rates)

`CurrencyExchangeService` hits the configured exchange API and caches the full rates table for 1 hour.

```php
use Unusualify\Modularity\Facades\CurrencyExchange;

$priceEur = 99.00;
$priceUsd = CurrencyExchange::convertTo($priceEur, 'USD');       // rounded, 2 dp
$priceUsd = CurrencyExchange::convertTo($priceEur, 'USD', 0);    // rounded, 0 dp
$priceUsd = CurrencyExchange::convertTo($priceEur, 'USD', 2, 'ceil');
```

::: warning Unsupported currencies throw
`convertTo()` throws `\Exception` when the target ISO is not present in the rates response. Wrap it in a `try` block in controllers that accept user-supplied currencies.
:::

## 5. Read raw exchange rates

```php
$rates = CurrencyExchange::fetchExchangeRates();
// ['USD' => 1.08, 'GBP' => 0.86, ...]

$usdRate = CurrencyExchange::getExchangeRate('USD');
```

The rates table is keyed by ISO 4217. The base currency is whatever is configured in `services.currency_exchange.base_currency` (default `EUR`).

## 6. Call from the frontend via HTTP

Modularous ships these endpoints via `CurrencyExchangeController` (see `routes/front.php`):

| Method | URI | Purpose |
|--------|-----|---------|
| `POST` | `/currency/fetch-rates` | Warm / refresh the rates cache |
| `POST` | `/currency/convert` | `{ amount, currency }` → `{ converted_amount, exchange_rate }` |
| `GET`  | `/currency/rate/{currency}` | `{ currency, rate }` |

Use from Vue / Axios:

```js
const { data } = await axios.post('/currency/convert', {
  amount: 99,
  currency: 'USD',
})
// data.converted_amount, data.exchange_rate
```

## 7. Use in `PricesTrait` (repository)

When your repository uses `PricesTrait` (e.g. via `HasPriceable`), the trait reads the active provider automatically:

- If `services.currency_exchange.active` is `true`: only the base-currency price is persisted from the form, and prices for **every enabled currency** are generated via `CurrencyExchange::convertTo()`.
- If `active` is `false`: whatever prices the form submitted are persisted as-is.

You rarely call the provider directly in this flow — just configure the flag. See [PricesTrait](/system-reference/backend/repository-traits/payment) for the detailed lifecycle.

## 8. Swap providers in tests

`CurrencyProviderInterface` is a contract, so tests can bind a stub:

```php
use Unusualify\Modularity\Contracts\CurrencyProviderInterface;

class FakeCurrencyProvider implements CurrencyProviderInterface
{
    public function findByIso4217(string $isoCode): ?object
    {
        return (object) ['id' => 1, 'iso_4217' => 'EUR', 'symbol' => '€'];
    }

    public function getCurrenciesForSelect(): array
    {
        return [['id' => 1, 'name' => '€', 'iso' => 'EUR']];
    }

    public function isAvailable(): bool
    {
        return true;
    }
}

$this->app->instance(CurrencyProviderInterface::class, new FakeCurrencyProvider);
```

For the exchange service, fake the HTTP layer:

```php
Http::fake([
    '*' => Http::response(['data' => ['USD' => 1.08, 'GBP' => 0.86]]),
]);
```

## Common Pitfalls

| Symptom | Cause | Fix |
|---------|-------|-----|
| Empty currency `<select>` in prod only | `SystemPricing` module not installed → `NullCurrencyProvider` active | Install the module or bind a custom provider |
| `Unsupported currency: XYZ` exception | ISO not in the rates response | Catch + fall back to the base currency, or validate ISO first |
| Stale rates after manual edit | 1-hour cache | `Cache::forget('exchange_rates')` or `php artisan cache:clear` |
| Only EUR shows in admin dropdown | `services.currency_exchange.active` is `true` (intended — conversion handles the rest) | Set `active` → `false` to expose all currencies |
| `getCurrenciesForSelect()` returns `[]` in tests | No binding for the interface in the test container | Bind a fake provider as shown above |

## See Also

- [Overview](./overview) — selection rules, configuration, binding
- [Custom Provider](./custom-provider) — build your own `CurrencyProviderInterface`
- [CurrencyExchangeService](/system-reference/backend/services/currency-exchange-service)
