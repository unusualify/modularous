---
sidebarPos: 2
sidebarTitle: HasPriceable
---

# HasPriceable

**Namespace**: `Unusualify\Modularity\Entities\Traits\HasPriceable`

Base pricing trait. Extends `Oobook\Priceable\Traits\HasPriceable` and `HasPriceableMutators` with currency-exchange support, language-based price conversion, and additional query scopes.

---

## Relationships

```php
public function prices(): MorphMany      // → All Price records for this model
public function basePrice(): MorphOne    // → Active Price for the current user's currency (with exchange rate applied when configured)
public function originalBasePrice(): MorphOne  // → Active Price for user's currency without exchange conversion
```

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getLanguageBasedPriceFactor` | `(): int` | Returns the rounding factor for language-based price conversion (10^`$languageBasedPricePower`) |

---

## Scopes

| Scope | Description |
|-------|-------------|
| `scopeHasBasePrice($query)` | Models that have at least one base price record |
| `scopeOrderByBasePrice($query, $direction = 'asc', $role = null)` | Orders by the current user currency's base price |
| `scopeOrderByCurrencyPrice($query, $currencyId, $direction = 'asc', $role = null)` | Orders by the price for a specific currency ID |

---

## Configuration

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$languageBasedPricePower` | `int` | `0` | Decimal power for language-based price rounding (e.g. `2` → round to nearest 100) |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\HasPriceable;

class Product extends Model
{
    use HasPriceable;
}

// Base price for the current user's currency
$product->basePrice;                    // Price model
$product->basePrice->raw_amount;        // integer amount

// Without exchange conversion
$product->originalBasePrice;

// Ordering
Product::hasBasePrice()->get();
Product::orderByBasePrice()->get();
Product::orderByBasePrice('desc')->get();
Product::orderByCurrencyPrice($eurId)->get();
```
