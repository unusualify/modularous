---
outline: deep
sidebarPos: 15
---

# Translation

The Translation feature adds locale-specific content via Astrotomic Translatable. It uses Entity traits + Repository trait. There is **no dedicated Hydrate** — translation is enabled per input with `translated: true`.

## Entity Traits: IsTranslatable and HasTranslation

Add both traits to your model:

```php
<?php

namespace Modules\Page\Entities;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasTranslation;
use Unusualify\Modularity\Entities\Traits\IsTranslatable;

class Page extends Model
{
    use HasTranslation, IsTranslatable;

    public $translatedAttributes = ['title', 'description', 'content'];
}
```

### IsTranslatable

- **isTranslatable($columns = null)** — Returns whether the model is translatable (has HasTranslation and translatedAttributes)

### HasTranslation

- Uses Astrotomic Translatable
- **translations** — Relation to translation records
- **translatedAttributes** — Array of attributes stored per locale

## Repository Trait: TranslationsTrait

Add `TranslationsTrait` to your repository:

```php
<?php

namespace Modules\Page\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\TranslationsTrait;

class PageRepository extends Repository
{
    use TranslationsTrait;

    public function __construct(Page $model)
    {
        $this->model = $model;
    }
}
```

### Methods

- **setColumnsTranslationsTrait** — Collects inputs with `translated => true`
- **prepareFieldsBeforeSaveTranslationsTrait** — Converts flat/translations fields into locale-keyed structure
- **getFormFieldsTranslationsTrait** — Populates `translations[attribute][locale]` from the model
- **filterTranslationsTrait** — Applies search in translations for translatable attributes
- **orderTranslationsTrait** — Orders by translated attributes
- **getPublishedScopesTranslationsTrait** — Returns `withActiveTranslations` scope

## Input Config

Mark inputs as translatable with `translated: true` on each input:

```php
'routes' => [
    'item' => [
        'inputs' => [
            [
                'name' => 'title',
                'type' => 'text',
                'label' => 'Title',
                'translated' => true,
            ],
            [
                'name' => 'description',
                'type' => 'textarea',
                'label' => 'Description',
                'translated' => true,
            ],
            [
                'name' => 'images',
                'type' => 'image',
                'label' => 'Images',
                'translated' => true,
            ],
        ],
    ],
],
```

### Supported Input Types

- text, textarea, wysiwyg
- image, file, filepond (with role/locale pivot)
- tagger, tag
- repeater (schema items can have `translated`)

## Hydrate

None. Each Hydrate (FileHydrate, ImageHydrate, TaggerHydrate, RepeaterHydrate, etc.) respects `translated` for locale-aware handling. The TranslationsTrait handles persistence and form field population.
