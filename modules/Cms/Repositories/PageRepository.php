<?php

namespace Modules\Cms\Repositories;

use Modules\Cms\Entities\Page;
use Modules\Cms\Repositories\Traits\CmrTrait;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\FilepondsTrait;
use Unusualify\Modularity\Repositories\Traits\FilesTrait;
use Unusualify\Modularity\Repositories\Traits\ImagesTrait;
use Unusualify\Modularity\Repositories\Traits\PublishableTrait;
use Unusualify\Modularity\Repositories\Traits\RepeatersTrait;
use Unusualify\Modularity\Repositories\Traits\RevisionsTrait;
use Unusualify\Modularity\Repositories\Traits\SlugsTrait;
use Unusualify\Modularity\Repositories\Traits\TranslatableMetadataTrait;
use Unusualify\Modularity\Repositories\Traits\TranslationsTrait;

class PageRepository extends Repository
{
    /**
     * TranslationsTrait must run before SlugsTrait so getFormFieldsTranslationsTrait does not wipe SlugsTrait output.
     * TranslatableMetadataTrait must come after TranslationsTrait (form pipeline + guards).
     */
    use TranslationsTrait,
        TranslatableMetadataTrait,
        RevisionsTrait,
        SlugsTrait,
        RepeatersTrait,
        FilesTrait,
        ImagesTrait,
        FilepondsTrait,
        CmrTrait,
        PublishableTrait;

    public function __construct(Page $model)
    {
        $this->model = $model;
    }
}
