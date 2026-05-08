---
sidebarPos: 20
sidebarTitle: ProfileController
---

# ProfileController

**File**: `src/Http/Controllers/ProfileController.php`  
**Namespace**: `Unusualify\Modularity\Http\Controllers`  
**Extends**: `BaseController`  
**Traits**: `ManageUtilities`

User profile management controller. Provides three editing sections — personal info, security (password), and company/billing — with optional email verification status display.

## Properties

| Property | Value | Description |
|----------|-------|-------------|
| `$namespace` | `'Modules\SystemUser'` | Fixed to the SystemUser module |
| `$moduleName` | `'Profile'` | — |
| `$routeName` | `'Profile'` | — |
| `$modelName` | `'User'` | — |

## Constructor

```php
public function __construct(
    Application $app,
    Request $request,
    UserRepository $userRepository,
    CompanyRepository $companyRepository
)
```

Removes the default `view` and `edit` permission middleware — profile pages are accessible to any authenticated user without extra permissions.

## Methods

### `edit($id = null, $submoduleId = null): View|Response`

Displays the profile edit page with three form sections:

| Section | Fields |
|---------|--------|
| **User info** | name, surname, email, avatar, locale |
| **Security** | current password, new password, confirmation |
| **Company** | company name, tax ID, address, billing details |

When the user's email is unverified, a "Verify Email" button is injected into the user info form. The company form section is hidden if `modularity.lock_company_edit` is `true`.

Delegates to `renderInertiaProfile()` when Inertia is active.

### `renderInertiaProfile(array $data): Response`

Renders the profile via Inertia, passing store variables, form sections, and verification status.

### `update($id = null, $submoduleId = null): JsonResponse`

Handles all three section submissions. Detects which section was submitted from the request payload and dispatches accordingly:

- **User info**: updates name, surname, locale, and avatar.
- **Security**: validates current password before updating.
- **Profile-level fields**: updates any other declared profile fields.

Logs an `updated` activity event on success.

### `display(): View|JsonResponse`

Returns the authenticated user's profile data (for the profile display page, not the edit form).

### `updateCompany(CompanyRequest $request): JsonResponse`

Updates the user's associated company billing information. Validates through `CompanyRequest`.

## Related

- `UserRepository` — data access for user records
- `CompanyRepository` — data access for company/billing records
