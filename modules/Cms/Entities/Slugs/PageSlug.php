<?php

namespace Modules\Cms\Entities\Slugs;

use Illuminate\Database\Eloquent\SoftDeletes;
use Unusualify\Modularity\Entities\Model;

class PageSlug extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'page_id',
        'slug',
        'locale',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getTable(): string
    {
        return modularityConfig('tables.cms_page_slugs', 'um_cms_page_slugs');
    }
}
