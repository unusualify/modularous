---
sidebarPos: 1
sidebarTitle: Overview
outline: deep
---

# Generics Overview

**Generics** are cross-cutting utilities and patterns that any module can use. Unlike [Module Features](/guide/module-features/overview) (which follow the Entity + Repository + Hydrate triple pattern), generics are lightweight traits and conventions applied wherever they're useful — controllers, arrays, route configs, models.

## At a Glance

| Generic | Layer | Solves | Entry point |
|---------|-------|--------|-------------|
| [Allowable](./allowable) | Controller trait | Role-based filtering of arrays / collections (menus, actions, widgets) | `use Allowable` + `allowedRoles` key |
| [Responsive Visibility](./responsive-visibility) | Controller trait | Show/hide array items per Vuetify breakpoint | `use ResponsiveVisibility` + `responsive` key |
| [File Storage with Filepond](./file-storage-with-filepond) | Model trait + input | One-to-many polymorphic file uploads via FilePond | `use HasFileponds` + `type: filepond` |
| [Relationships](./relationships) | CLI + runtime | Eloquent relationship generation and conventions | `--relationships` on `make:model` / `make:route` |

## Decision Guide

**Need to filter a list by user role?** → [Allowable](./allowable)

**Need to hide menu items on mobile?** → [Responsive Visibility](./responsive-visibility)

**Need users to upload files (avatar, attachments)?** → [File Storage with Filepond](./file-storage-with-filepond)

**Defining a new module and its relations?** → [Relationships](./relationships)

**Need the full feature triple (Entity + Repository + Hydrate)?** → Check [Module Features](/guide/module-features/overview) instead.

---

## Quick Examples

### Allowable — Filter by Role

```php
use Unusualify\Modularity\Traits\Allowable;

class NavigationController extends Controller
{
    use Allowable;

    public function items()
    {
        return $this->getAllowableItems([
            ['title' => 'Home', 'route' => 'home'],
            ['title' => 'Admin', 'route' => 'admin', 'allowedRoles' => ['admin']],
        ]);
    }
}
```

Items without `allowedRoles` are public. See [Allowable](./allowable) for closures, guards, and custom search keys.

---

### Responsive Visibility — Breakpoint Control

```php
use Unusualify\Modularity\Traits\ResponsiveVisibility;

class MenuController extends Controller
{
    use ResponsiveVisibility;

    public function items()
    {
        return $this->getResponsiveItems([
            ['title' => 'Desktop Search', 'responsive' => ['hideBelow' => 'md']],
            ['title' => 'Mobile Menu',    'responsive' => ['hideAbove' => 'md']],
        ]);
    }
}
```

Applies Vuetify `d-{breakpoint}-*` classes. See [Responsive Visibility](./responsive-visibility) for all modifiers (`hideOn`, `showOn`, `hideBelow`, `hideAbove`, `breakpoints`).

---

### Filepond — Upload in Three Lines

Model:

```php
use Unusualify\Modularity\Entities\Traits\HasFileponds;

class Ticket extends Model
{
    use HasFileponds;
}
```

Route config:

```php
'inputs' => [
    ['type' => 'filepond', 'name' => 'attachments', 'max' => 5],
]
```

Repository:

```php
use Unusualify\Modularity\Repositories\Traits\FilepondsTrait;

class TicketRepository extends Repository
{
    use FilepondsTrait;
}
```

See [File Storage with Filepond](./file-storage-with-filepond) for storage mechanics and [Files and Media](/guide/module-features/files-and-media) for the full triple pattern.

---

### Relationships — Define at Generation Time

```bash
# Model-level (adds method to parent model)
php artisan modularity:make:model Package Billing \
  --relationships="belongsToMany:Feature"

# Route-level (creates pivot + migration + reverse relation)
php artisan modularity:make:route Billing packages \
  --relationships="Feature:belongsToMany,position:integer:unsigned:index"
```

See [Relationships](./relationships) for full grammar, field types, and modifiers.

---

## Composing Generics

Generics are independent, so you can stack them. The most common combo is **Allowable + ResponsiveVisibility** on the same menu:

```php
use Unusualify\Modularity\Traits\{Allowable, ResponsiveVisibility};

class MenuController extends Controller
{
    use Allowable, ResponsiveVisibility;

    public function items()
    {
        $items = [
            [
                'title' => 'Admin Panel',
                'allowedRoles' => ['admin'],
                'responsive' => ['hideBelow' => 'md'],
            ],
        ];

        // Order matters: filter by role first, then apply CSS classes
        return $this->getResponsiveItems(
            $this->getAllowableItems($items)
        );
    }
}
```

---

## Where Generics Live

| Generic | FQCN |
|---------|------|
| Allowable | `Unusualify\Modularity\Traits\Allowable` |
| ResponsiveVisibility | `Unusualify\Modularity\Traits\ResponsiveVisibility` |
| HasFileponds | `Unusualify\Modularity\Entities\Traits\HasFileponds` |
| FilepondsTrait | `Unusualify\Modularity\Repositories\Traits\FilepondsTrait` |

## Related

- [Module Features](/guide/module-features/overview) — full feature patterns (Entity + Repository + Hydrate)
- [Files and Media](/guide/module-features/files-and-media) — Files / Images / Filepond side-by-side
- [Hydrates](/system-reference/hydrates) — how form schema is generated
