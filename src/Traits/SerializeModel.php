<?php

namespace Unusualify\Modularity\Traits;

use Illuminate\Database\Eloquent\Model;

trait SerializeModel
{
    /**
     * Serialize a model with its relationships for caching.
     *
     * @param Model $model
     * @return array
     */
    public function serializeModel(Model $model): array
    {
        $data = [
            'attributes' => $model->getAttributes(),
            'relations' => [],
            'class' => get_class($model),
        ];

        // Serialize loaded relationships
        foreach ($model->getRelations() as $relationName => $relationValue) {
            if ($relationValue instanceof \Illuminate\Database\Eloquent\Model) {
                // Single model relationship
                $data['relations'][$relationName] = [
                    'type' => 'model',
                    'data' => $this->serializeModel($relationValue),
                ];
            } elseif ($relationValue instanceof \Illuminate\Support\Collection || is_array($relationValue)) {
                // Collection of models relationship
                $data['relations'][$relationName] = [
                    'type' => 'collection',
                    'data' => collect($relationValue)->map(function ($item) {
                        return $item instanceof \Illuminate\Database\Eloquent\Model ? $this->serializeModel($item) : $item;
                    })->toArray(),
                ];
            } else {
                // Other types (null, primitives, etc.)
                $data['relations'][$relationName] = [
                    'type' => 'other',
                    'data' => $relationValue,
                ];
            }
        }

        return $data;
    }

    /**
     * Unserialize a model with its relationships from cache.
     *
     * @param array $data
     * @return Model
     */
    public function unserializeModel(array $data): Model
    {
        $modelClass = $data['class'];
        $model = new $modelClass;

        // Restore attributes
        $model->setRawAttributes($data['attributes'], true);

        // Restore relationships
        foreach ($data['relations'] as $relationName => $relationData) {
            if ($relationData['type'] === 'model') {
                // Single model relationship
                $relatedModel = $this->unserializeModel($relationData['data']);
                $model->setRelation($relationName, $relatedModel);
            } elseif ($relationData['type'] === 'collection') {
                // Collection of models relationship
                $relatedModels = collect($relationData['data'])->map(function ($item) {
                    return is_array($item) && isset($item['class'])
                        ? $this->unserializeModel($item)
                        : $item;
                });
                $model->setRelation($relationName, $relatedModels);
            } else {
                // Other types
                $model->setRelation($relationName, $relationData['data']);
            }
        }

        // Mark as existing (from database)
        $model->exists = true;

        // Sync original attributes to prevent "dirty" state
        $model->syncOriginal();

        return $model;
    }
}
