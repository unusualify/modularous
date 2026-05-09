---
sidebarPos: 7
sidebarTitle: Relationship Traits
---

# Relationship Repository Traits

These traits handle the repository-side persistence and form hydration for assignment, authorization, and creator tracking features. They pair with the corresponding [Entity Traits](../entity-traits/relationships/overview) (`Assignable`, `HasAuthorizable`, `HasCreator`).

---

## AssignmentTrait

**Namespace**: `Unusualify\Modularous\Repositories\Traits\AssignmentTrait`

Manages assignment-related form field hydration, default query filtering, and data table filter tabs. Uses the `Allowable` trait internally for role-based permission checks on filter visibility.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$hasUserAwareCacheAssignmentTrait` | `bool` | Signals the cache layer that this trait produces user-specific results |

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `setColumnsAssignmentTrait` | Registers inputs matching `type: assignment` |
| `getFormFieldsAssignmentTrait` | Sets each assignment column to the model's primary key |
| `filterAssignmentTrait` | Applies `everAssignedToYourRoleOrHasAuthorization` scope to index queries |

### Table Filters

`getTableFiltersAssignmentTrait()` returns filter tabs for the data table:

| Filter | Slug | Scope |
|--------|------|-------|
| My Assignments | `my-assignments` | `isActiveAssignee` |
| Your Role Assignments | `your-role-assignments` | `isActiveAssigneeForYourRole` |
| Completed Assignments | `completed-assignments` | `completedAssignments` |
| Pending Assignments | `pending-assignments` | `pendingAssignments` |
| Your Completed Assignments | `your-completed-assignments` | `yourCompletedAssignments` |
| Team Completed Assignments | `team-completed-assignments` | `teamCompletedAssignments` |
| Your Pending Assignments | `your-pending-assignments` | `yourPendingAssignments` |
| Team Pending Assignments | `team-pending-assignments` | `teamPendingAssignments` |

::: info
The "Completed Assignments" and "Pending Assignments" tabs are only visible to users with `superadmin`, `admin`, or `manager` roles (checked via `Allowable`).
:::

### Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `getAssignments` | `(mixed $id): Collection` | Returns all `Assignment` records for a given model ID, ordered by `created_at desc` |

### Usage

```php
use Unusualify\Modularous\Repositories\Traits\AssignmentTrait;

class TaskRepository extends Repository
{
    use AssignmentTrait;
}

$repo->getAssignments($taskId);
```

---

## AuthorizableTrait

**Namespace**: `Unusualify\Modularous\Repositories\Traits\AuthorizableTrait`

Hydrates authorization record data into form fields and provides authorized/unauthorized filter tabs.

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `getFormFieldsAuthorizableTrait` | If the model has an existing authorization record, populates `authorized_id` and `authorized_type` into form fields. Casts `authorized_id` to integer when the authorized model does not use UUIDs. |

### Table Filters

`getTableFiltersAuthorizableTrait()` returns:

| Filter | Slug | Scope | Condition |
|--------|------|-------|-----------|
| Authorized | `authorized` | `hasAnyAuthorization` | Only if model `hasAuthorizationUsage()` |
| Unauthorized | `unauthorized` | `unauthorized` | Only if model `hasAuthorizationUsage()` |
| Your Authorizations | `your-authorizations` | `isAuthorizedToYou` | Always shown |

### Usage

```php
use Unusualify\Modularous\Repositories\Traits\AuthorizableTrait;

class ReportRepository extends Repository
{
    use AuthorizableTrait;
}
```

---

## CreatorTrait

**Namespace**: `Unusualify\Modularous\Repositories\Traits\CreatorTrait`

Applies creator-based access scoping on index queries, hydrates the `custom_creator_id` form field, and prepends a creator input to the form schema.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$hasUserAwareCacheCreatorTrait` | `bool` | Signals the cache layer that results depend on the current user |

### Lifecycle Hooks

| Hook | Description |
|------|-------------|
| `filterCreatorTrait` | Adds `hasAccessToCreation` scope to limit results to the user's own records (or authorized records) |
| `getFormFieldsCreatorTrait` | Populates `custom_creator_id` from the model's `creator` relation when the schema defines it |
| `prependFormSchemaCreatorTrait` | Prepends a `type: creator` input to the form schema, displaying the creator info at the top of forms |

### Usage

```php
use Unusualify\Modularous\Repositories\Traits\CreatorTrait;

class ArticleRepository extends Repository
{
    use CreatorTrait;
}

// Index queries automatically filter by creator access
// Form fields include the creator display
```
