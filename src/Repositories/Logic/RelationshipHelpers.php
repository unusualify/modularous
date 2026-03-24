<?php

namespace Unusualify\Modularity\Repositories\Logic;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

trait RelationshipHelpers
{
    /**
     * @param array|string|null $relations
     */
    public function getDefinedRelations($relations = null): array
    {
        $relationNamespace = "Illuminate\Database\Eloquent\Relations";

        $relationClassesPattern = '~' . preg_quote($relationNamespace, '~') . '~';

        if ($relations) {
            if (is_array($relations)) {
                $relationNamespaces = implode('|', Arr::map($relations, function ($relationName) use ($relationNamespace) {
                    return preg_quote($relationNamespace . '\\' . $relationName, '~');
                }));
                $relationClassesPattern = '~^(' . $relationNamespaces . ')$~';
            } elseif (is_string($relations)) {
                $relationClassesPattern = '~^' . preg_quote($relationNamespace . '\\' . $relations, '~') . '$~';
            }
        }

        $reflector = new \ReflectionClass($this->getModel());

        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))->reduce(function ($carry, $method) use ($relationClassesPattern) {

            if ($method->getNumberOfParameters() < 1) {
                if ($method->hasReturnType()) {
                    if (preg_match($relationClassesPattern, ($returnType = $method->getReturnType()))) {
                        $carry[] = $method->name;
                    }
                } else {

                }
            }

            return $carry;
        }, []);
    }

    /**
     * @param array|string|null $relations
     */
    public function definedRelations($relations = null): array
    {
        if (method_exists($this->model, 'definedRelations')) {
            return $this->model->definedRelations($relations);
        }

        return $this->getDefinedRelations($relations);
    }

    public function getRelationForeignKey($relation)
    {
        if ($relation instanceof BelongsTo) {
            return $this->getForeignKeyBelongsTo($relation);
        } elseif ($relation instanceof BelongsToMany) {
            return $this->getForeignKeyBelongsToMany($relation);
        } elseif ($relation instanceof HasMany) {
            return $this->getForeignKeyHasMany($relation);
        } else {
            throw new \InvalidArgumentException('Invalid relation type');
        }
    }

    /**
     * @return string
     */
    private function getForeignKeyBelongsTo(BelongsTo $relation)
    {
        return $relation->getForeignKeyName();
    }

    /**
     * @return string
     */
    private function getForeignKeyBelongsToMany(BelongsToMany $relation)
    {
        return $relation->getRelatedPivotKeyName();
    }

    /**
     * @return string
     */
    private function getForeignKeyHasMany(HasMany $relation)
    {
        return $relation->getForeignKeyName();
    }
}
