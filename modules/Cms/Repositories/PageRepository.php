<?php

namespace Modules\Cms\Repositories;

use Modules\Cms\Entities\Page;
use Modules\Cms\Repositories\Traits\CmrTrait;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\FilepondsTrait;
use Unusualify\Modularous\Repositories\Traits\FilesTrait;
use Unusualify\Modularous\Repositories\Traits\ImagesTrait;
use Unusualify\Modularous\Repositories\Traits\PublishableTrait;
use Unusualify\Modularous\Repositories\Traits\RepeatersTrait;
use Unusualify\Modularous\Repositories\Traits\RevisionsTrait;
use Unusualify\Modularous\Repositories\Traits\SlugsTrait;
use Unusualify\Modularous\Repositories\Traits\TranslatableMetadataTrait;
use Unusualify\Modularous\Repositories\Traits\TranslationsTrait;

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
