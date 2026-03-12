---
outline: deep
sidebarPos: 19
---

# Payment

The Payment feature links models to price and payment information via HasPriceable. It uses Entity trait + Repository trait. Price inputs use `PriceHydrate`; payment service selection uses `PaymentServiceHydrate`.

## Entity Trait: HasPayment

This trait defines a relationship between a model and its price information by leveraging the [Unusualify/Priceable](https://github.com/unusualify/priceable) package:

```php
<?php

namespace Modules\Package\Entities;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasPayment;

class PackageCountry extends Model
{
    use HasPayment;
}
```

With this trait, each model record can have multiple price records with different price types, currencies and VAT rates. Related models must have HasPriceable trait.

### Relationships and Accessors

- **paymentPrice()** — morphOne to Price (role: payment)
- **paidPrices()** — Paid price records
- **is_paid**, **is_unpaid**, **is_partially_paid**, **payment_status_formatted** — appended attributes

## Repository Trait: PaymentTrait

This trait creates a single price for all related model records under the same relation with the same currency. Define `$paymentTraitRelationName` in the repository:

```php
<?php

namespace Modules\Package\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\PaymentTrait;

class PackageCountryRepository extends Repository
{
    use PaymentTrait;

    public $paymentTraitRelationName = 'packages';

    public function __construct(PackageCountry $model)
    {
        $this->model = $model;
    }
}
```

The related model (e.g. Package) must have HasPriceable trait. PaymentTrait uses PricesTrait for price persistence.

See [Unusualify/Payable](https://github.com/unusualify/payable) for payment flow details.

## Input Config

### Price Input

Use `PriceHydrate` for price fields:

```php
[
    'name' => 'prices',
    'type' => 'price',
    'label' => 'Price',
],
```

### Payment Service Input

For payment service selection (SystemPayment module), use `payment-service`:

```php
[
    'name' => 'payment_service',
    'type' => 'payment-service',
],
```

Payment service inputs are often added by PaymentTrait to form schema rather than declared in route inputs.

## Hydrate: PriceHydrate and PaymentServiceHydrate

### PriceHydrate

- **type**: `input-price`
- **items**: From CurrencyProvider (currencies)
- Optional: `vatRates`, `hasVatRate`

### PaymentServiceHydrate

- **type**: `input-payment-service`
- **items**: Published external/transfer payment services
- **supportedCurrencies**: Payment currencies with payment services
- **currencyCardTypes**: Card types per currency
- **transferFormSchema**: Schema for bank transfer (filepond, checkbox)
- **paymentUrl**, **checkoutUrl**, **completeUrl**: Payment flow routes



