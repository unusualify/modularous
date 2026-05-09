---
outline: deep
sidebarPos: 11
---

# Singular

The Singular feature enforces a single record per type (singleton pattern). It is **entity-only** — no repository trait or Hydrate.

## Entity Trait: IsSingular

Add the `IsSingular` trait to your model:

```php
<?php

namespace Modules\Settings\Entities;

use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\IsSingular;

class SiteSettings extends Model
{
    use IsSingular;

    protected $fillable = ['site_name', 'site_description', 'contact_email'];
}
```

### How It Works

- Uses a global scope `SingularScope` so only one record exists per type
- Stores data in a `modularous_singletons` table (configurable via `tables.singletons`)
- `singleton_type` stores the model class
- `content` stores fillable attributes as JSON (excluding `singleton_type`, `content`)

### Methods

- **single()** — Returns the singleton record (creates if none exists)
- **scopePublished** — Filters by `content->published`
- **isPublished()** — Returns whether the record is published

### Boot Logic

- On **creating**: Sets `singleton_type`; moves fillable attributes into `content`; unsets fillable from attributes
- On **updating**: Same as creating
- On **retrieved**: Loads `content` back into attributes; unsets `content` and `singleton_type`

### Example

```php
$settings = SiteSettings::single();
$settings->site_name = 'My Site';
$settings->save();
```

## Repository Trait

None. The singleton is managed at the entity level.

## Input Config

None. Use standard form inputs for the model's fillable attributes; the form targets the singleton route.

## Hydrate

None.
