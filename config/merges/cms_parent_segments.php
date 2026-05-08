<?php

return [
    /**
     * URL parent prefix bindings: one row per model class + locale (unique on target_model_class + locale).
     * {@code normalized_prefix} may be left blank for a locale-root homepage (see {@see \Modules\Cms\Support\ParentSegmentBindingValidator} exclusivity rule).
     *
     * @see \Modules\Cms\Services\CmsParentSegmentResolver
     * @see \Modules\Cms\Entities\ParentSegment
     */
    'enabled' => env('MODULARITY_CMS_PARENT_SEGMENTS_ENABLED', true),
];
