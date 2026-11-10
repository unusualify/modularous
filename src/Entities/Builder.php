<?php

namespace Unusualify\Modularous\Entities;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as LaravelModel;

class Builder extends EloquentBuilder
{
    /**
     * Hydrate the rows with deferred `retrieved` events, so `retrievedBatch{Trait}` hooks can
     * load what the per-model `retrieved` listeners need for the whole result set at once.
     *
     * @param array $items
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function hydrate(array $items)
    {
        $instance = $this->newModelInstance();

        if (count($items) < 2 || ! $instance instanceof Model || ($batchMethods = $instance::retrievedBatchMethods()) === []) {
            return parent::hydrate($items);
        }

        $models = Model::withDeferredRetrievedEvents(fn () => array_map(function ($item) use ($instance) {
            $model = $instance->newFromBuilder($item);
            $model->preventsLazyLoading = LaravelModel::preventsLazyLoading();

            return $model;
        }, $items));

        $collection = $instance->newCollection($models);

        foreach ($batchMethods as $method) {
            $instance->{$method}($collection);
        }

        foreach ($models as $model) {
            $model->fireRetrievedEvent();
        }

        return $collection;
    }
}
