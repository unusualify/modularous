---
sidebarPos: 1
sidebarTitle: HasPayment
---

# HasPayment

**Namespace**: `Unusualify\Modularity\Entities\Traits\HasPayment`

Full payment lifecycle management. Internally uses `HasPriceable`. Tracks payment relationships and exposes human-readable status computed attributes. Provides global scopes that prefetch payment existence flags to avoid lazy-load queries.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `saving` | Removes virtual payment attributes (`_price`, `priceExcludingVatFormatted`, etc.) |

---

## Relationships

```php
public function paymentPrices(): MorphMany        // → All Price records with role 'payment'
public function paymentPrice(): MorphOne          // → Latest Price with role 'payment'
public function initialPayablePrice(): MorphOne   // → Oldest Price with role 'payment'
public function payablePrice(): MorphOne          // → Latest unpaid Price (no completed payment)
public function paidPrices(): MorphMany           // → Prices with a COMPLETED payment
public function providedPrices(): MorphMany       // → Prices with a PROVISION payment
public function refundedPrices(): MorphMany       // → Prices with a REFUNDED payment
public function payment(): HasOneThrough          // → Latest Payment record through Price
public function payments(): HasManyThrough        // → All Payment records through Price
```

---

## Appended Attributes

Appended via `initializeHasPayment()`:

| Attribute | Type | Description |
|-----------|------|-------------|
| `is_paid` | `bool` | `true` if at least one completed payment exists |
| `is_unpaid` | `bool` | `true` if there is a payable (unpaid) price |
| `is_partially_paid` | `bool` | `true` if both paid and unpaid conditions are true |
| `is_provided` | `bool` | `true` if a provision payment exists |
| `is_refunded` | `bool` | `true` if a refunded payment exists |
| `payment_status_formatted` | `string` | Vuetify chip HTML with color and label |

---

## Computed Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `total_cost_excluding_vat` | `int` | Sum of `raw_amount` across all payment prices |
| `total_cost_including_vat` | `int` | Sum of `total_amount` across all payment prices |
| `total_cost_excluding_vat_formatted` | `string\|null` | Formatted total (excl. VAT) |
| `total_cost_including_vat_formatted` | `string\|null` | Formatted total (incl. VAT) |
| `initial_price_excluding_vat` | `int` | `raw_amount` of the first payment price |
| `initial_price_excluding_vat_formatted` | `string\|null` | Formatted initial price |
| `payable_price_excluding_vat` | `int\|null` | Current payable price (excl. VAT) |
| `payable_price_including_vat` | `int\|null` | Current payable price (incl. VAT) |

---

## Global Scopes

Registers via `addGlobalScopesHasPayment()`:
- `paid_prices_exists` — `withExists('paidPrices')`
- `payable_price_exists` — `withExists('payablePrice')`
- `provided_prices_exists` — `withExists('providedPrices')`
- `refunded_prices_exists` — `withExists('refundedPrices')`

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getPaymentRelations` | `(): array` | Returns the `$hasPaymentRelations` property as an array of relation names |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\HasPayment;

class Order extends Model
{
    use HasPayment;
}

// Payment relationships
$order->payment;
$order->payments()->latest()->get();
$order->paidPrices;
$order->paymentPrice;

// Status checks
$order->is_paid;                     // true / false
$order->is_partially_paid;
$order->is_refunded;

// Formatted
$order->payment_status_formatted;    // Vuetify chip HTML
$order->total_cost_including_vat_formatted;

// Raw amounts
$order->total_cost_excluding_vat;    // integer
```
