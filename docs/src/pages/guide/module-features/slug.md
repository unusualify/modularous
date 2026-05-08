---
outline: deep
sidebarPos: 12
---

# Slug

The Slug feature provides URL-friendly slugs per locale. It uses Entity trait + Repository trait. There is **no dedicated Hydrate** — slug fields are derived from translatable inputs and `slugAttributes`.

## Entity Trait: HasSlug

Add the `HasSlug` trait to your model:

```php
<?php

namespace Modules\Page\Entities;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasSlug;

class Page extends Model
{
    use HasSlug;

    protected $slugAttributes = [
        ['title'],
    ];
}
```

### Relationships

- **slugs()** — hasMany to the Slug model (e.g. `PageSlug`)

### Methods

- **getSlugModelClass** — Returns the Slug model class
- **getSlugAttributes** — Returns `$slugAttributes` (fields used to generate slugs)
- **setSlugs** — Creates or updates slugs from model attributes
- **scopeExistsSlug** — Find by active slug and locale
- **scopeExistsInactiveSlug** — Find by slug (any active state)
- **scopeExistsFallbackLocaleSlug** — Find by slug in fallback locale

### Boot Logic

- On **saved**: Calls `setSlugs()`
- On **restored**: Calls `setSlugs($restoring = true)`

### slugAttributes

Define which translatable attributes drive the slug. Each item can be an array of field names (e.g. `['title']`) or a single field. Slugs are generated per locale from these fields.

## Repository Trait: SlugsTrait

Add `SlugsTrait` to your repository:

```php
<?php

namespace Modules\Page\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\SlugsTrait;

class PageRepository extends Repository
{
    use SlugsTrait;

    public function __construct(Page $model)
    {
        $this->model = $model;
    }
}
```

### Methods

- **afterSaveSlugsTrait** — Persists slugs from `$fields['slugs']` per locale
- **afterDeleteSlugsTrait** — Deletes slugs on model delete
- **afterRestoreSlugsTrait** — Restores slugs on model restore
- **getFormFieldsSlugsTrait** — Populates `translations.slug` from existing slugs
- **existsSlug** — Find model by slug (with published/visible scopes)
- **existsSlugPreview** — Find model by slug (including inactive)

## Input Config

Slug is not configured as a standalone input. It is derived from:

1. **Translatable fields** — The model must use `IsTranslatable` / `HasTranslation` with fields listed in `slugAttributes`
2. **Form schema** — Slug fields appear under `translations.slug` in the form; the SlugsTrait populates them from `$object->slugs`

The slug input is typically rendered as part of the translation/locale tabs, bound to `translations.slug[locale]`.

## Hydrate

None. Slug persistence is handled by SlugsTrait; the form schema for slug fields comes from the translation/locale structure, not a dedicated Hydrate.
