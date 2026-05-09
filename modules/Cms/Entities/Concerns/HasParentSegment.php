<?php

namespace Modules\Cms\Entities\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Modules\Cms\Entities\ParentSegment;

/**
 * Opt-in marker for Eloquent models that participate in URL parent-segment bindings
 * (shared path prefixes per model class + locale).
 *
 * Used by {@see \Modules\Cms\Repositories\Traits\ParentSegmentTrait},
 * {@see \Unusualify\Modularous\Modularous::getModuleRouteModelSelectItems()},
 * and CMS slug validation when resolving public paths.
 */
trait HasParentSegment
{
    public static function supportsParentSegmentBindings(): bool
    {
        return true;
    }

    public function parentSegments(): Collection
    {
        return ParentSegment::where('target_model_class', static::class)->get();
    }
}
