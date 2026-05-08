<?php

namespace Modules\Cms\Entities\Concerns;

/**
 * Content module route (CMR): CMS panel routes that participate in parent-segment URL bindings and UrlRoute syncing.
 *
 * Composes {@see HasParentSegment}. Pair repositories with {@see \Modules\Cms\Repositories\Traits\CMRTrait}.
 */
trait IsCmr
{
    use HasParentSegment;
}
