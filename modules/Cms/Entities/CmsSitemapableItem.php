<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Override row: links a sitemap “bucket” to a public page (or other urlable) for {@code changefreq} / {@code priority}.
 */
class CmsSitemapableItem extends Model
{
    protected $fillable = [
        'sitemap_id',
        'sitemapable_type',
        'sitemapable_id',
        'changefreq',
        'priority',
    ];

    protected $casts = [
        'priority' => 'float',
    ];

    public function getTable(): string
    {
        return modularityConfig('tables.cms_sitemapables', 'um_cms_sitemapables');
    }

    public function sitemap(): BelongsTo
    {
        return $this->belongsTo(Sitemap::class, 'sitemap_id');
    }

    public function sitemapable(): MorphTo
    {
        return $this->morphTo();
    }
}
