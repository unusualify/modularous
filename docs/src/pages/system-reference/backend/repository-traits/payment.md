---
sidebarPos: 5
sidebarTitle: Payment Traits
---

# Payment Repository Traits

These traits manage price record persistence and payment workflow orchestration. `PricesTrait` handles basic price CRUD through morph relations, while `PaymentTrait` builds on top of it to calculate totals, integrate payment services, and render the payment action modal.

---

## PricesTrait

**Namespace**: `Unusualify\Modularity\Repositories\Traits\PricesTrait`

Creates, updates, and deletes morphed `Price` records for the model. Supports automatic currency exchange conversion when the currency exchange service is active.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$formatableColumns` | `array` | Price columns included in form field output: `id`, `raw_amount`, `currency_id`, `vat_rate_id`, `price_type_id`, `discount_percentage` |

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `setColumnsPricesTrait` | Registers inputs matching `type: price` |
| `afterSavePricesTrait` | Creates or updates `Price` records per role. When currency exchange is active, auto-converts to all enabled currencies. Deletes orphaned prices when exchange is not active. |
| `getFormFieldsPricesTrait` | Loads prices by role, maps them to formatable column arrays. Returns default price structure for roles without existing prices. |

### After-Save Flow

For each price column (excluding `payment`):

1. Fetches existing prices for the role.
2. For each submitted price:
   - **Existing** (`id` present) → updates the price record.
   - **New** → creates a new price with default attributes merged.
3. If currency exchange is active:
   - Converts the base amount to each enabled currency via `CurrencyExchange::convertTo()`.
   - Creates or updates the converted price record per currency.
4. If currency exchange is **not** active:
   - Deletes prices whose IDs are no longer in the submitted data.

### Form Field Hydration

Prices are loaded filtered by the user's session currency (when exchange is active) and grouped by role. Each price is mapped to a subset of columns, with amount fields cast to `float`.

When no price exists for a role, a default structure is returned:

```php
[
    'price_value' => 0.00,
    'raw_amount'  => 0.00,
    'currency_id' => $sessionCurrencyId,
    // ... default attributes from Price model
]
```

### Usage

```php
use Unusualify\Modularity\Repositories\Traits\PricesTrait;

class ProductRepository extends Repository
{
    use PricesTrait;
}

// Price inputs detected from schema:
// ['type' => 'price', 'name' => 'base_price']
// ['type' => 'price', 'name' => 'wholesale_price']
```

---

## PaymentTrait

**Namespace**: `Unusualify\Modularity\Repositories\Traits\PaymentTrait`

Orchestrates the full payment lifecycle: calculating totals from related priceable models, creating payment records via payment services, and rendering a payment modal action on forms. Internally uses `PricesTrait`.

### Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$paymentTraitRelationName` | `mixed` | `null` | Override the relation name used for payment price |
| `$paymentTraitDefaultCurrencyId` | `int` | `1` | Fallback currency ID when none is set |
| `$requiredTrait` | `string` | `HasPriceable` | Entity trait required on related models for price aggregation |
| `$snapshotTrait` | `string` | `HasSnapshot` | Snapshot trait checked for source-based pricing |

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `afterSavePaymentTrait` | Handles three scenarios: direct payment price update, auto-calculation from related models, and payment service integration |
| `getFormFieldsPaymentTrait` | Populates `fields['payment']` from the model's payment relation |

### After-Save Logic

The trait handles three distinct cases in order:

**1. Direct payment price update** — when `fields['payment_price']` is submitted:
- If the existing price is unpaid → updates it directly.
- If the existing price is paid → creates a replica with new values (preserves payment history).

**2. Auto-calculation from relations** — when no payment price exists or `force_payment_update` is set:
- Iterates the model's `$paymentRelations`.
- For each related model with `HasPriceable` (or `HasSnapshot` → source with `HasPriceable`), sums `originalBasePrice->raw_amount`.
- Creates or updates the payment price with the total.

**3. Payment service integration** — when `fields['payment_service_id']` is submitted:
- Loads the payment service configuration.
- Updates VAT rate and discount percentage on the payment price if provided.
- For transferrable services, calls `$paymentPrice->updateOrNewPayment()` with email, creator, receipts, description, status, and currency data.

### Form Actions

`getFormActionsPaymentTrait()` returns a modal-based "Pay" button:

```php
[
    'paymentTrait' => [
        'type'      => 'modal',
        'label'     => 'Pay',
        'icon'      => 'mdi-credit-card-outline',
        'color'     => 'success',
        'endpoint'  => route('admin.system.system_payment.pay'),
        'schema'    => [...],  // hidden price_id + payment-service input
        'conditions' => [
            ['payment.status', 'not in', ['completed', 'provision', 'refunded']]
        ],
        'hideOnCondition' => true,
    ],
]
```

The button is hidden when payment status is `COMPLETED`, `PROVISION`, or `REFUNDED`.

### Customization Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getFormActionsConditionsForPayment` | `(): array` | Override on the model to add extra conditions for the Pay button visibility |
| `getFormActionPropsForPaymentTrait` | `(): array` | Override on the model to merge extra props into the Pay action |
| `defaultPaymentPriceFields` | `(): array` | Returns default field values for new payment prices. Resolves `price_type_id`, `vat_rate_id`, and `currency_id` by name/slug/iso. |
| `getDefaultPaymentPriceFields` | `(): array` | Override in your repository to provide custom defaults |
| `getPaymentFormSchema` | `(): array` | Returns the form schema for the payment modal |

### Usage

```php
use Unusualify\Modularity\Repositories\Traits\PaymentTrait;

class OrderRepository extends Repository
{
    use PaymentTrait;

    public function getDefaultPaymentPriceFields(): array
    {
        return [
            'price_type_id' => 'one-time',
            'vat_rate_id'   => 'standard',
            'currency_id'   => 'USD',
        ];
    }
}
```
