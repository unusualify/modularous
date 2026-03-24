<?php

namespace Unusualify\Modularity\Events\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait EventChanges
{
    /**
     * The changed attributes.
     *
     * @var array
     */
    protected $changedAttributes = [];

    /**
     * The changed relationships.
     *
     * @var array
     */
    protected $changedRelationships = [];

    public function setupEventChanges()
    {
        if ($this->model instanceof Model) {
            $this->changedAttributes = $this->model->getChanges();
            $this->changedRelationships = method_exists($this->model, 'getChangedRelationships')
                ? $this->model->getChangedRelationships()
                : [];
        }
    }

    public function wasChanged($values = null)
    {
        if (empty($values)) {
            return count($this->changedAttributes) > 0 || count($this->changedRelationships) > 0;
        }

        foreach (Arr::wrap($values) as $value) {
            if (array_key_exists($value, $this->changedAttributes) || array_key_exists($value, $this->changedRelationships)) {
                return true;
            }
        }

        return false;
    }
}
