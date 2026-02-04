<?php

namespace Unusualify\Modularity\Traits;

trait CheckSnapshot
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $related
     * @return bool
     */
    protected function isSnapshotRelation($related)
    {
        return in_array('Oobook\Snapshot\Traits\HasSnapshot', class_uses_recursive($related));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $related
     * @return string
     */
    protected function getSnapshotSourceForeignKey($related)
    {
        return $related->getSnapshotSourceForeignKey();
    }
}
