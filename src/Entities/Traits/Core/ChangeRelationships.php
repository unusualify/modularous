<?php

namespace Unusualify\Modularity\Entities\Traits\Core;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

trait ChangeRelationships
{
    protected $changedRelationships = [];

    public function setChangedRelationships($relationships)
    {
        $this->changedRelationships = $relationships;
    }

    public function addChangedRelationships($relationshipName, $relationship)
    {
        $this->changedRelationships[$relationshipName] = $relationship;
    }

    public function mergeChangedRelationships($relationshipName, $relationships)
    {
        $this->changedRelationships[$relationshipName] = ($this->changedRelationships[$relationshipName] ?? Collection::make())->merge($relationships);
    }

    public function getChangedRelationships()
    {
        return $this->changedRelationships;
    }

    public function wasChangedRelationships($relationships = null)
    {
        return $this->hasChangedRelationships(
            $this->getChangedRelationships(), is_array($relationships) ? $relationships : func_get_args()
        );
    }

    protected function hasChangedRelationships($changes, $relationships = null)
    {
        if (empty($relationships)) {
            return count($changes) > 0;
        }

        foreach (Arr::wrap($relationships) as $relationship) {
            if (array_key_exists($relationship, $changes)) {
                return true;
            }
        }

        return false;
    }
}
