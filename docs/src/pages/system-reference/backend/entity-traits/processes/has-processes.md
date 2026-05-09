---
sidebarPos: 1
sidebarTitle: HasProcesses
---

# HasProcesses

**Namespace**: `Unusualify\Modularous\Entities\Traits\HasProcesses`

Aggregates `Process` records from configured child (relationship) models into a parent model. Useful when a parent entity (e.g., a `Project`) needs to see all processes across its child entities (e.g., `pressReleasePackages`, `addons`). Uses raw SQL subqueries to avoid standard `HasMany` constraint conflicts.

---

## Configuration

```php
// In your model, define which child relationships contribute processes:
protected static array $hasProcessesRelationships = [
    'pressReleasePackages',
    'pressReleasePackageAddons',
];
```

---

## Relationships

```php
public function processes(): HasMany           // → All Process records across configured relationships
public function confirmedProcesses(): HasMany  // → Confirmed processes only
public function rejectedProcesses(): HasMany   // → Rejected processes only
```

::: warning Implementation note
These relationships use `Relation::noConstraints()` with a raw SQL subquery. They cannot be used with standard Laravel eager-loading patterns that rely on a single `processable_id` constraint.
:::

---

## Methods

| Method | Signature | Description |
|--------|-----------|-------------|
| `processableRelationships` | `(): array` (static) | Returns `$hasProcessesRelationships` (or empty array if not defined) |

---

## Usage

```php
use Unusualify\Modularous\Entities\Traits\HasProcesses;

class Project extends Model
{
    use HasProcesses;

    protected static array $hasProcessesRelationships = [
        'projectTasks',
        'projectMilestones',
    ];

    public function projectTasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function projectMilestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }
}

// All processes across child models
$project->processes()->get();
$project->confirmedProcesses()->count();
$project->rejectedProcesses()->latest()->first();
```
