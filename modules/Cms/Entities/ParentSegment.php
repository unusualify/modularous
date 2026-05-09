<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * One URL prefix row per model class + locale (unique on target_model_class + locale).
 *
 * @property string $target_model_class
 * @property string $locale Empty string = all locales
 * @property string $normalized_prefix May be deliberately empty for locale-root URLs (homepage) when enabled.
 * @property string|null $admin_label
 * @property bool $enabled
 * @property int $sort_order
 */
class ParentSegment extends EloquentModel
{
    protected $fillable = [
        'target_model_class',
        'locale',
        'normalized_prefix',
        'admin_label',
        'enabled',
        'sort_order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getTable(): string
    {
        return modularousConfig('tables.cms_parent_segment_bindings', 'um_cms_parent_segment_bindings');
    }
}
