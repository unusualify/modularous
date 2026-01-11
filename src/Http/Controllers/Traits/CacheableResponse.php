<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\ModularityCache;
use Unusualify\Modularity\Traits\Cache\Cacheable;
use Unusualify\Modularity\Traits\SerializeModel;

trait CacheableResponse
{
    use Cacheable, SerializeModel;

    /**
     * Whether to track relationships for granular cache invalidation.
     */
    protected bool $trackResponseRelations = true;

    protected function getCacheableFormItem($id = null, callable $formItemCallback): array
    {
        if(!$this->shouldUseCache('formItem') || !$id) {
            return $formItemCallback();
        }

        return $this->rememberCache($formItemCallback, 'formItem', ['id' => $id]);
    }

    /**
     * Extract relationship IDs from paginator items.
     * Returns array of ['ModelClass' => [id1, id2, ...]]
     */
    protected function extractResponseRelationIds($paginator): array
    {
        $relations = [];

        // Get items from paginator
        $items = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
            ? $paginator->getCollection()
            : (is_array($paginator) ? collect($paginator) : $paginator);

        foreach ($items as $item) {
            if ($item instanceof Model) {
                $itemRelations = $this->extractModelRelationIds($item);

                foreach ($itemRelations as $relatedClass => $id) {
                    if (! isset($relations[$relatedClass])) {
                        $relations[$relatedClass] = [];
                    }
                    if (! in_array($id, $relations[$relatedClass])) {
                        $relations[$relatedClass][] = $id;
                    }
                }
            }
        }

        return $relations;
    }

    /**
     * Extract relationship IDs from a single model.
     * Returns array of ['ModelClass' => id]
     */
    protected function extractModelRelationIds(Model $model): array
    {
        $relations = [];

        $attributes = $model->getAttributes();

        foreach ($attributes as $key => $value) {
            if ($value !== null && Str::endsWith($key, '_id')) {
                // Convert foreign key to potential model class name
                $relationMethod = Str::camel(Str::beforeLast($key, '_id'));

                if (method_exists($model, $relationMethod)) {
                    try {
                        $relation = $model->$relationMethod();
                        if ($relation) {
                            $relatedClass = get_class($relation->getRelated());
                            $relations[$relatedClass] = $value;

                            continue;
                        }
                    } catch (\Exception $e) {
                        // Relationship method exists but threw an error
                    }
                }

                // Fallback: use generic class name based on foreign key
                $relatedName = Str::studly(Str::beforeLast($key, '_id'));
                $relations[$relatedName] = $value;
            }
        }

        return $relations;
    }
}

