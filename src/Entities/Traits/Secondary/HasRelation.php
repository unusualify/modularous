<?php

namespace Unusualify\Modularous\Entities\Traits\Secondary;

trait HasRelation
{
    protected static function bootHasRelation()
    {
        static::forceDeleting(function ($model) {
            // dd($model);
            // dd($model);
            // $model->setToLastPosition();
            // $model->
        });
    }
}
