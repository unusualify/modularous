<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Unusualify\Modularous\Entities\Model;

class Sitemap extends Model
{
    protected $fillable = [
        'slug',
    ];

    public function getTable(): string
    {
        return modularousConfig('tables.cms_sitemaps', 'um_cms_sitemaps');
    }

    /**
     * Per-model XML overrides: {@code changefreq} / {@code priority} (nullable in DB → jeneratörde config default’ları).
     */
    public function sitemapableItems(): HasMany
    {
        return $this->hasMany(CmsSitemapableItem::class, 'sitemap_id');
    }
}
