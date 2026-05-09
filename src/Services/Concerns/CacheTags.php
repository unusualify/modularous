<?php

namespace Unusualify\Modularous\Services\Concerns;

use Illuminate\Support\Str;

/**
 * Cache tag generator methods.
 *
 * @requires method getPrefix() - Get cache prefix
 */
trait CacheTags
{
    /**
     * Get the cache prefix.
     */
    abstract protected function getPrefix(): string;

    /**
     * Get tags for a module.
     */
    public function getModuleTags(string $moduleName, bool $onlyModule = false): array
    {
        $prefix = $this->getPrefix();
        $moduleName = Str::studly($moduleName);

        return [
            ...($onlyModule ? [] : ["{$prefix}"]),
            "{$prefix}:{$moduleName}",
        ];
    }

    /**
     * Get tags for a specific route (submodule) within a module.
     */
    public function getModuleRouteTags(string $moduleName, string $moduleRouteName, bool $onlyRoute = false): array
    {
        $prefix = $this->getPrefix();
        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);

        return [
            ...($onlyRoute ? [] : ["{$prefix}"]),
            ...($onlyRoute ? [] : ["{$prefix}:{$moduleName}"]),
            "{$prefix}:{$moduleName}:{$moduleRouteName}",
        ];
    }

    /**
     * Get tags for a specific cache type within a route.
     */
    public function getTypeTags(string $moduleName, string $moduleRouteName, string $type): array
    {
        $prefix = $this->getPrefix();
        $moduleName = Str::studly($moduleName);
        $moduleRouteName = Str::studly($moduleRouteName);

        return [
            "{$prefix}",
            "{$prefix}:{$moduleName}",
            "{$prefix}:{$moduleName}:{$moduleRouteName}",
            "{$prefix}:{$moduleName}:{$moduleRouteName}:{$type}",
        ];
    }

    /**
     * Generate a relationship tag for a specific model and ID.
     * Format: rel:{ModelClass}:{id}
     */
    public function generateRelationTag(string $modelClass, $id): string
    {
        $prefix = $this->getPrefix();
        $modelName = class_basename($modelClass);

        return "{$prefix}:rel:{$modelName}:{$id}";
    }

    /**
     * Generate relationship tags from an array of related models.
     * Input format: ['ModelClass' => id, 'ModelClass' => [id1, id2], ...]
     */
    public function generateRelationTags(array $relations): array
    {
        $tags = [];

        foreach ($relations as $modelClass => $ids) {
            $ids = is_array($ids) ? $ids : [$ids];
            foreach ($ids as $id) {
                if ($id !== null) {
                    $tags[] = $this->generateRelationTag($modelClass, $id);
                }
            }
        }

        return $tags;
    }
}
