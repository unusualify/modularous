---
sidebarPos: 4
sidebarTitle: Custom Provider
outline: deep
---

# Building a Custom Currency Provider

Implement `CurrencyProviderInterface` when neither `NullCurrencyProvider` nor `SystemPricingCurrencyProvider` fits — e.g. you want to back currency data with a third-party API, a different Eloquent model, or a hard-coded list.

**Time**: ~10 minutes for a working provider.

## Checklist

1. Implement the three interface methods in a new class.
2. Bind your class to the interface in a service provider.
3. (Optional) Cache expensive reads.
4. Verify by resolving from the container and calling each method.

## Step 1 — Scaffold the class

Place the class wherever fits your project layout — for this example, `app/Services/Currency/`:

```php
<?php

declare(strict_types=1);

namespace App\Services\Currency;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Unusualify\Modularous\Contracts\CurrencyProviderInterface;

class ExternalApiCurrencyProvider implements CurrencyProviderInterface
{
    public function findByIso4217(string $isoCode): ?object
    {
        $iso = mb_strtoupper($isoCode);

        return Cache::remember(
            "external_currency_{$iso}",
            now()->addHours(6),
            function () use ($iso) {
                $response = Http::get("https://example.com/api/currencies/{$iso}");

                if (! $response->ok()) {
                    return null;
                }

                return (object) $response->json();
                // Expected shape: ['id' => int, 'iso_4217' => 'USD', 'symbol' => '$', ...]
            },
        );
    }

    public function getCurrenciesForSelect(): array
    {
        return Cache::remember(
            'external_currencies_list',
            now()->addHours(6),
            function () {
                $response = Http::get('https://example.com/api/currencies');

                if (! $response->ok()) {
                    return [];
                }

                return collect($response->json())
                    ->map(fn ($row) => [
                        'id'   => $row['id'],
                        'name' => $row['symbol'],
                        'iso'  => $row['iso_4217'],
                    ])
                    ->all();
            },
        );
    }

    public function isAvailable(): bool
    {
        return ! empty(config('services.example_currency.api_key'));
    }
}
```

## Step 2 — Bind in a service provider

Add to `AppServiceProvider::register()` (or any other service provider that runs before the HTTP kernel):

```php
use App\Services\Currency\ExternalApiCurrencyProvider;
use Unusualify\Modularous\Contracts\CurrencyProviderInterface;

public function register(): void
{
    $this->app->bind(
        CurrencyProviderInterface::class,
        ExternalApiCurrencyProvider::class,
    );
}
```

Use `singleton()` instead of `bind()` if your implementation holds state between calls (e.g. an in-memory cache):

```php
$this->app->singleton(CurrencyProviderInterface::class, ExternalApiCurrencyProvider::class);
```

### Binding conditionally

If you want to fall back to the package default when your configuration is incomplete:

```php
$this->app->bind(CurrencyProviderInterface::class, function ($app) {
    if (empty(config('services.example_currency.api_key'))) {
        return new \Unusualify\Modularous\Services\Currency\NullCurrencyProvider;
    }

    return new ExternalApiCurrencyProvider;
});
```

## Step 3 — Verify

Tinker session:

```php
php artisan tinker

>>> $p = app(\Unusualify\Modularous\Contracts\CurrencyProviderInterface::class);
>>> get_class($p);
=> "App\\Services\\Currency\\ExternalApiCurrencyProvider"

>>> $p->isAvailable();
=> true

>>> $p->findByIso4217('USD');
=> {#... id: 1, iso_4217: "USD", symbol: "$" ...}

>>> $p->getCurrenciesForSelect();
=> [['id' => 1, 'name' => '$', 'iso' => 'USD'], ...]
```

## Return-Shape Contract

Other Modularous code only relies on three things — keep them stable:

| Method | Non-null return must have… |
|--------|---------------------------|
| `findByIso4217()` | Either an object with **public readable fields** or an Eloquent model. Consumers typically read `->id`, `->iso_4217`, `->symbol` / `->name`. |
| `getCurrenciesForSelect()` | Array of arrays with keys exactly `id`, `name`, `iso`. |
| `isAvailable()` | Strict `bool`. Return `false` **fast** — callers may check this in hot paths. |

Break the shape and consumers (select inputs, `PricesTrait`) silently misbehave.

## Testing Your Provider

```php
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Unusualify\Modularous\Contracts\CurrencyProviderInterface;

class ExternalApiCurrencyProviderTest extends TestCase
{
    public function test_it_returns_usd(): void
    {
        Http::fake([
            'example.com/api/currencies/USD' => Http::response([
                'id'        => 42,
                'iso_4217'  => 'USD',
                'symbol'    => '$',
            ]),
        ]);

        $currency = app(CurrencyProviderInterface::class)->findByIso4217('usd');

        $this->assertSame('USD', $currency->iso_4217);
        $this->assertSame('$',   $currency->symbol);
    }
}
```

## Replacing `SystemPricingCurrencyProvider`

If the SystemPricing module is installed but you want to override its binding:

1. Register your provider in a service provider with a **higher** `$defer` / load priority than SystemPricing's.
2. Or call `$this->app->extend()` from `AppServiceProvider::boot()`:

```php
public function boot(): void
{
    $this->app->extend(
        CurrencyProviderInterface::class,
        fn ($existing, $app) => new ExternalApiCurrencyProvider,
    );
}
```

`boot()` runs after all `register()` hooks, so your binding wins deterministically.

## See Also

- [Overview](./overview) — how provider selection works
- [Usage Patterns](./usage-patterns) — consuming the provider
- [NullCurrencyProvider](./null-currency-provider) — reference fallback
- [SystemPricingCurrencyProvider](./system-pricing-currency-provider) — reference implementation
