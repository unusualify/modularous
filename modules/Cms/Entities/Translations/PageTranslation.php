<?php

namespace Modules\Cms\Entities\Translations;

use Modules\Cms\Entities\Page;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Support\TranslatableMetadata;

class PageTranslation extends Model
{
    public $baseModuleModel = Page::class;

    protected $fillable = [
        'title',
        'slug_segment',
        'excerpt',
        'content',
        'active',
        'locale',
        ...TranslatableMetadata::TRANSLATED_ATTRIBUTES,
    ];

    protected $casts = [
        // 'robots_index' => 'boolean',
        // 'robots_follow' => 'boolean',
        'active' => 'boolean',
    ];

    public function getTable(): string
    {
        return modularityConfig('tables.cms_page_translations', 'um_cms_page_translations');
    }
}
