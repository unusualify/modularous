<?php

namespace Unusualify\Modularity\Entities\Traits\Core;

use Unusualify\Modularity\Facades\RelationshipGraph;

/**
 * Trait HasCacheDependents
 *
 * This trait provides the interface for defining cache dependents.
 * The actual cache invalidation is handled by CacheObserver (via HasCaching trait).
 *
 * Dependents are resolved from multiple sources (in order of priority):
 * 1. Relationship graph (automatic discovery from getEloquentRelationships)
 * 2. Property $cacheDependents defined in the model
 * 3. Method getCacheDependents() override in the model
 * 4. Global config: modularity.cache.dependencies.{ModelName}
 *
 * Example: Company model is displayed in PressRelease lists.
 * When Company is updated, PressRelease cache should be invalidated.
 *
 * Usage in model:
 * ```php
 * class Company extends Model
 * {
 *     use HasCaching, HasCacheDependents;
 *
 *     // Option 1: Define via property (explicit modules)
 *     protected array $cacheDependents = ['press_release', 'invoice'];
 *
 *     // Option 2: Define dynamically via method override
 *     public function getCacheDependents(): array
 *     {
 *         return array_merge(
 *             parent::getCacheDependents(),
 *             ['custom_module']
 *         );
 *     }
 * }
 * ```
 *
 * Note: If you only use HasCaching trait, the relationship graph will
 * automatically discover dependents from getEloquentRelationships().
 * Use this trait when you need to add explicit dependents that
 * cannot be auto-discovered.
 */
trait HasCacheDependents
{
    /**
     * Get the list of modules that depend on this model's cached data.
     * This merges graph-discovered, property-defined, and config-defined dependents.
     */
    public function getCacheDependents(): array
    {
        $dependents = [];
        $modelClass = get_class($this);

        // 1. Get dependents from relationship graph (automatic discovery)
        try {
            $graphDependents = RelationshipGraph::getAffectedModules($modelClass);
            $dependents = array_merge($dependents, $graphDependents);
        } catch (\Throwable $e) {
            // Graph might not be available yet during boot
        }

        // 2. Check for property-defined dependents
        if (property_exists($this, 'cacheDependents') && is_array($this->cacheDependents)) {
            $dependents = array_merge($dependents, $this->cacheDependents);
        }

        // 3. Check global config for this model class (supports full class name and short name)
        $configDependents = $this->getConfigDefinedDependents($modelClass);
        $dependents = array_merge($dependents, $configDependents);

        return array_unique($dependents);
    }

    /**
     * Get dependents from global config.
     * Uses full class names only.
     */
    protected function getConfigDefinedDependents(string $modelClass): array
    {
        $dependencies = config('modularity.cache.dependencies', []);

        if (empty($dependencies) || ! isset($dependencies[$modelClass])) {
            return [];
        }

        return (array) $dependencies[$modelClass];
    }

    /**
     * Get dependents only from the relationship graph (automatic discovery).
     */
    public function getGraphDiscoveredDependents(): array
    {
        try {
            return RelationshipGraph::getAffectedModules(get_class($this));
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get dependents only from manual configuration (property + config).
     */
    public function getManualDependents(): array
    {
        $dependents = [];
        $modelClass = get_class($this);

        if (property_exists($this, 'cacheDependents') && is_array($this->cacheDependents)) {
            $dependents = array_merge($dependents, $this->cacheDependents);
        }

        $configDependents = $this->getConfigDefinedDependents($modelClass);

        return array_unique(array_merge($dependents, $configDependents));
    }

    /**
     * Add a dependent module dynamically.
     */
    public function addCacheDependent(string $module): static
    {
        if (! property_exists($this, 'cacheDependents')) {
            $this->cacheDependents = [];
        }

        if (! in_array($module, $this->cacheDependents)) {
            $this->cacheDependents[] = $module;
        }

        return $this;
    }

    /**
     * Check if this model has any cache dependents defined.
     */
    public function hasCacheDependents(): bool
    {
        return ! empty($this->getCacheDependents());
    }
}
