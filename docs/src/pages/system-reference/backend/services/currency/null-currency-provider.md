---
sidebarPos: 1
sidebarTitle: NullCurrencyProvider
---

# NullCurrencyProvider

**File**: `src/Services/Currency/NullCurrencyProvider.php`  
**Implements**: `CurrencyProviderInterface`

`NullCurrencyProvider` is the default fallback implementation used when no pricing module is installed. All methods return safe empty values so that code calling `CurrencyExchangeService` does not need to null-check the provider.

## Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `findByIso4217(string $isoCode): ?object` | `null` | Always returns null — no currency model available |
| `getCurrenciesForSelect(): array` | `[]` | Returns empty array — no currencies to select |
| `isAvailable(): bool` | `false` | Signals that currency functionality is not available |

## When It Is Used

The `NullCurrencyProvider` is bound automatically when the `SystemPricing` module (or any other module that provides a `CurrencyProviderInterface` binding) is not installed. Downstream code should check `isAvailable()` before relying on currency data:

```php
$provider = app(CurrencyProviderInterface::class);

if ($provider->isAvailable()) {
    $currency = $provider->findByIso4217('USD');
}
```
