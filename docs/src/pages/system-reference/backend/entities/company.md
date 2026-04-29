---
sidebarPos: 7
sidebarTitle: Company
---

# Company

**File**: `src/Entities/Company.php`  
**Namespace**: `Unusualify\Modularity\Entities`  
**Extends**: `Model`  
**Traits**: `HasFactory`, `HasSpreadable`

Organisation/company record. Users belong to a company, which holds billing and address information. Supports the `HasSpreadable` trait for extending fields dynamically via JSON.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `name` | `string` | Company name |
| `address` | `string` | Street address |
| `city` | `string` | City |
| `state` | `string` | State/province |
| `country_id` | `int` | Country reference |
| `zip_code` | `string` | Postal code |
| `phone` | `string` | Phone number |
| `vat_number` | `string` | VAT number |
| `tax_id` | `string` | Tax ID |

## Relationships

### `users(): HasMany`

All users belonging to this company.

### `country(): BelongsTo`

The company's country (from `SystemUtility` module).

### `paymentCountry(): BelongsTo`

Payment-specific country record (from `SystemPayment` module), resolved via `country_id`.

## Accessors

| Accessor | Type | Description |
|----------|------|-------------|
| `country_name` | `string\|null` | Country display name |
| `company_type` | `string` | `'personal'` or `'corporate'` (derived from spread `is_personal` flag) |
| `is_personal_company` | `bool` | Whether the company is a personal account |
| `is_corporate_company` | `bool` | Whether the company is corporate |
| `is_valid` | `bool` | Whether all required billing fields are filled |
| `is_valid_formatted` | `string` | HTML chip showing Yes/No validation status |

## Validation

The `is_valid` accessor checks different required fields based on company type:

- **Personal**: address, city, state, zip_code, country_id
- **Corporate**: name, tax_id, email, address, country_id, city, state, zip_code

## Related

- [User](./user) — users belonging to this company
- [HasSpreadable](/system-reference/backend/entity-traits/model-behavior/has-spreadable) — dynamic JSON field extension
- [RegisterController](/system-reference/backend/http/controllers/auth/register-controller) — creates a company during registration
