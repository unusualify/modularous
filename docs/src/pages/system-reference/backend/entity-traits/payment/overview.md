---
sidebarPos: 7
sidebarTitle: Payment Traits
sidebarGroupTitle: Payment Traits
---

# Payment Traits

Two complementary traits handle pricing and payment state. `HasPriceable` provides the base pricing layer (extends `oobook/priceable`). `HasPayment` builds on top of it with full payment relationship management and computed status attributes.

| Trait | Description |
|-------|-------------|
| [HasPriceable](./has-priceable) | Base pricing via `MorphMany` to `Price`, with currency exchange and ordering scopes |
| [HasPayment](./has-payment) | Full payment lifecycle: paid/unpaid/refunded status, payment relationships, formatted attributes |
