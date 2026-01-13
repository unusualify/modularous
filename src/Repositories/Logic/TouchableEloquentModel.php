<?php

namespace Unusualify\Modularity\Repositories\Logic;

trait TouchableEloquentModel
{
    protected $mustTouchEloquentModel = false;

    public function mustTouchEloquentModel()
    {
        $this->mustTouchEloquentModel = true;
    }

    public function letEloquentModelBeTouched($value)
    {
        if ($this->mustTouchEloquentModel === false && $value === true) {
            $this->mustTouchEloquentModel();
        }
    }

    public function touchEloquentModel(\Illuminate\Database\Eloquent\Model $object)
    {
        if ($this->mustTouchEloquentModel) {
            $object->touch();
        }elseif($object->mustTouchable === true) {
            $object->touch();
        }

        return $object;
    }
}
