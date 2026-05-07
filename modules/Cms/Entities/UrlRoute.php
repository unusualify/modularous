<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Registry row: which entity owns a public path for a given locale (after normalization).
 * Plain Eloquent (no package SoftDeletes) so the table stays a simple unique index.
 *
 * @property string $locale
 * @property string $normalized_path
 * @property string|null $kind
 */
class UrlRoute extends EloquentModel
{
    public const KIND_PAGE_PUBLIC = 'page_public';

    public const KIND_REDIRECT_SOURCE = 'redirect_source';

    protected $fillable = [
        'locale',
        'normalized_path',
        'urlable_type',
        'urlable_id',
        'kind',
    ];

    public function getTable(): string
    {
        return modularityConfig('tables.cms_url_routes', 'um_cms_url_routes');
    }

    /**
     * Owning model (e.g. Page, Redirect).
     */
    public function urlable(): MorphTo
    {
        return $this->morphTo();
    }
}
