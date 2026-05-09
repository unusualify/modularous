---
sidebarPos: 24
sidebarTitle: Payment Service
---

# Payment Service

The `payment-service` input type renders `VInputPaymentService`, a full payment selector that displays available payment methods (credit card, transfer, external gateways), handles currency selection, VAT calculation, discount percentages, and transaction fee display. It integrates with the `SystemPayment` module.

> [!IMPORTANT]
> This component requires the `SystemPayment` and `SystemPricing` modules to be installed and the `modularous.default_payment_service` config key to be set.

## Hydrate

**Class:** `PaymentServiceHydrate`
**Config type:** `payment-service`
**Output type:** `input-payment-service` → `VInputPaymentService`

The hydrate:
- Loads `supportedCurrencies` from `PaymentCurrency` (filtered by user country if `useCountryBasedVatRates` is enabled)
- Loads `items` (external + transfer payment services) with their currencies
- Builds `currencyCardTypes` mapping ISO 4217 codes to card type logos
- Builds a `transferFormSchema` (hidden fields + Filepond receipt upload + TOS checkbox)
- Auto-resolves `paymentUrl`, `checkoutUrl`, and `completeUrl` from named routes
- Sets `includeTransactionFee` and `useCountryBasedVatRates` from Modularous config
- Sets `currencyConversionEndpoint` to `route('currency.convert')`

## Usage

```php
[
    'type'  => 'payment-service',
    'name'  => 'payment',
    'label' => 'Payment Method',
]
```

## Schema Defaults

| Key | Default | Description |
|-----|---------|-------------|
| `itemValue` | `'id'` | Field used as the payment service value |
| `itemTitle` | `'name'` | Field displayed for each payment service |
| `default` | `[]` | No pre-selected service |
| `default_payment_service` | config value | From `modularous.default_payment_service` |
| `useCountryBasedVatRates` | config value | From `Modularous::shouldUseCountryBasedVatRates()` |
| `includeTransactionFee` | config value | From `Modularous::shouldIncludeTransactionFee()` |

## See Also

- [Price](/guide/form-inputs/input-price) — Numeric price input with currency selection
- [Hydrates reference](/system-reference/hydrates) — Resolution table and schema contract
