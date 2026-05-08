---
sidebarPos: 4
sidebarTitle: HasCompany
---

# Core\HasCompany

**Namespace**: `Unusualify\Modularity\Entities\Traits\Core\HasCompany`

Associates a model (typically `User`) with a `Company` record. Auto-creates the `Company` on first save when a `saving_company_name` attribute is present. Appends several computed attributes for company display and billing state.

---

## Boot Behavior

| Event | Action |
|-------|--------|
| `creating` | If `saving_company_name` is set, captures it and removes it from attributes |
| `updating` | Removes `saving_company_name` from attributes |
| `saved` | If creating and company name was captured, creates a new `Company` and sets `company_id` via `updateQuietly` |

---

## Relationship

```php
public function company(): BelongsTo   // → Company model
```

---

## Appended Attributes

Appended via `initializeHasCompany()` (unless `$noCompanyAppends = true`):

| Attribute | Type | Description |
|-----------|------|-------------|
| `company_name` | `string` | Company name or empty string |
| `name_with_company` | `string` | `"User Name (Company Name)"` |
| `email_with_company` | `string` | `"email@example.com (Company Name)"` |
| `valid_company` | `bool` | Whether the company is valid |
| `show_billing_banner` | `bool` | `true` if user is a client, company is invalid, and billing is not disabled |

---

## Computed Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `company_exists` | `bool` | Pre-computed from `withExists('company')` global scope |
| `company_type` | `string` | Company type (`companyType`) or `'corporate'` if no company |

---

## Scopes

| Scope | Description |
|-------|-------------|
| `scopeCompanyUser($query)` | Records with a non-null `company_id` |

---

## Global Scopes

Registers `company_exists` via `addGlobalScopesHasCompany()`.

---

## Configuration

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$savingCompanyFieldName` | `string` | `'saving_company_name'` | Virtual fillable field name that triggers auto-company creation |
| `$noCompanyAppends` | `bool` | `false` | Set to `true` to disable auto-appended company attributes |

---

## Usage

```php
use Unusualify\Modularity\Entities\Traits\Core\HasCompany;

class User extends Authenticatable
{
    use HasCompany;
}

// Read company
$user->company;
$user->company_name;
$user->name_with_company;     // "Jane Doe (Acme Corp)"
$user->valid_company;         // bool

// Auto-create company on user creation
$user = User::create([
    'name' => 'Jane',
    'email' => 'jane@example.com',
    'saving_company_name' => 'New Corp',
]);
$user->company;               // Company model with name "New Corp"

// Query
User::companyUser()->get();
```
