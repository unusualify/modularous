<?php

namespace Unusualify\Modularous\Traits;

use Illuminate\Database\Eloquent\Model;

trait CheckSnapshot
{
    /**
     * @param Model $related
     * @return bool
     */
    protected function isSnapshotRelation($related)
    {
        return in_array('Oobook\Snapshot\Traits\HasSnapshot', class_uses_recursive($related));
    }

    /**
     * @param Model $related
     * @return string
     */
    protected function getSnapshotSourceForeignKey($related)
    {
        return $related->getSnapshotSourceForeignKey();
    }
}
