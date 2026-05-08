---
sidebarPos: 26
sidebarTitle: Price
---

# Price

The `price` input type renders `VInputPrice`, a currency-aware numeric input for entering prices. It supports multiple currency rows, an optional VAT rate selector, an optional discount percentage field, and displays a formatted total when VAT or discount is active.

> [!IMPORTANT]
> This component requires the `SystemPricing` module. Currencies are loaded via the `CurrencyProviderInterface` contract.

## Hydrate

**Class:** `PriceHydrate`
**Config type:** `price`
**Output type:** `input-price` → `VInputPrice`

The hydrate:
- Defaults `name` to `prices` and `label` to `"Prices"`
- Sets `priceInputName` from `Price::$priceSavingKey` (typically `price_value`)
- Builds the `default` array from `Price::defaultAttributes()` with the first available currency
- Loads `items` (currencies) via `CurrencyProviderInterface::getCurrenciesForSelect()`
- When `hasVatRate: true`, loads `vatRates` from `VatRateRepository`
- Sets `clearable: false`

## Usage

### Basic price field

```php
[
    'type'  => 'price',
    'name'  => 'prices',
    'label' => 'Price',
]
```

### With VAT rate selector

```php
[
    'type'       => 'price',
    'name'       => 'prices',
    'hasVatRate' => true,
]
```

### With discount

```php
[
    'type'        => 'price',
    'name'        => 'prices',
    'hasDiscount' => true,
    'hasVatRate'  => true,
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `name` | `'prices'` | Form field name |
| `col` | `{cols:6, sm:5, md:4}` | Default column width |
| `priceInputName` | `Price::$priceSavingKey` | Key within each price row |
| `clearable` | `false` | Cannot be cleared |
| `showVatRate` | `true` | Show the VAT rate selector (when `vatRates` are present) |
| `hasDiscount` | `false` | Show a discount percentage field |
| `numberMultiplier` | `100` | Internal multiplier for integer storage |

## See Also

- [Payment Service](/guide/form-inputs/input-payment-service) — Full payment method selector
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
