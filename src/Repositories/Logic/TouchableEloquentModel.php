<?php

namespace Unusualify\Modularity\Repositories\Logic;

use Illuminate\Database\Eloquent\Model;

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

    public function touchEloquentModel(Model $object)
    {
        if ($this->mustTouchEloquentModel) {
            $object->touch();
        } elseif ($object->mustTouchable === true) {
            $object->touch();
        }

        return $object;
    }
}
