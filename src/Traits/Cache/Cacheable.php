<?php

namespace Unusualify\Modularity\Traits\Cache;

use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\ModularityCache;
use Unusualify\Modularity\Traits\Moduleable;

trait Cacheable
{
    use Moduleable, CacheKeyGenerators;

    /**
     * Whether caching is enabled for this instance.
     */
    protected bool $cacheEnabled = true;

    /**
     * The module name for cache key generation (override).
     */
    protected ?string $cacheModuleName = null;

    /**
     * The route name for cache key generation (override).
     */
    protected ?string $cacheModuleRouteName = null;

    /**
     * Whether to prevent dependent warming.
     */
    public bool $dependentWarmingEnabled = true;

    /**
     * Check if caching should be used.
     */
    public function shouldUseCache(?string $type = null): bool
    {
        return $this->cacheEnabled && ModularityCache::isEnabled($this->getCacheModuleName(), $this->getCacheModuleRouteName(), $type);
    }

    public function withCache(bool $enabled = true): static
    {
        $this->cacheEnabled = $enabled;

        return $this;
    }

    public function withoutCache(): static
    {
        return $this->withCache(false);
    }

    public function getSelfCacheEnabled(): bool
    {
        return $this->cacheEnabled ?? true;
    }

    public function shouldWarmDependentModules(): bool
    {
        return $this->dependentWarmingEnabled;
    }

    public function preventDependentWarming(bool $prevent = true): static
    {
        $this->dependentWarmingEnabled = ! $prevent;

        return $this;
    }

    public function enableDependentWarming(): static
    {
        return $this->preventDependentWarming(false);
    }

    /**
     * Get the module name for cache keys.
     * Uses ManageModuleRoute trait's getModuleName() if available.
     */
    public function getCacheModuleName()
    {
        if ($this->cacheModuleName !== null) {
            return $this->cacheModuleName;
        }

        // Use ManageModuleRoute trait method if available
        if ($this instanceof \Unusualify\Modularity\Contracts\ModuleableInterface && ($moduleName = $this->getModuleName())) {
            $this->cacheModuleName = $moduleName;
        } elseif (method_exists($this, 'getModuleName')) {
            $this->cacheModuleName = $this->getModuleName();
        } elseif ($this instanceof \Unusualify\Modularity\Entities\Model) {
            if (preg_match('/Modules\\\\([^\\\\]+)\\\\/', get_class($this), $matches)) {
                $this->cacheModuleName = $matches[1];
            }
        }

        return $this->cacheModuleName;
    }

    /**
     * Get the route name (submodule) for cache keys.
     * Uses ManageModuleRoute trait's getRouteName() if available.
     */
    public function getCacheModuleRouteName()
    {
        if ($this->cacheModuleRouteName !== null) {
            return $this->cacheModuleRouteName;
        }

        // Use ManageModuleRoute trait method if available
        if ($this instanceof \Unusualify\Modularity\Contracts\ModuleableInterface && ($routeName = $this->getRouteName())) {
            $this->cacheModuleRouteName = $routeName;
        } elseif (method_exists($this, 'getRouteName')) {
            $this->cacheModuleRouteName = $this->getRouteName();
        } elseif ($this instanceof \Unusualify\Modularity\Entities\Model) {
            $this->cacheModuleRouteName = Str::studly(class_basename($this));
        }

        return $this->cacheModuleRouteName;
    }

    /**
     * Set the module name for cache keys.
     */
    public function setCacheModuleName(string $moduleName): static
    {
        $this->cacheModuleName = $moduleName;

        return $this;
    }

    /**
     * Set the route name for cache keys.
     */
    public function setCacheModuleRouteName(string $routeName): static
    {
        $this->cacheModuleRouteName = $routeName;

        return $this;
    }

    /**
     * Get the cache TTL for a specific type.
     */
    public function getCacheTtl(string $type): int
    {
        return ModularityCache::getTtl($type, $this->getCacheModuleName(), $this->getCacheModuleRouteName());
    }

    public function generateTypeCacheKey(string $type, array $data): string
    {
        [$type, $specifierKey, $data] = $this->resolveCacheSpecifiers($type, $data);
        $moduleName = $this->getCacheModuleName();
        $moduleRouteName = $this->getCacheModuleRouteName();

        return $this->createCacheKey($moduleName, $moduleRouteName, $specifierKey, $data);
    }

    /**
     * Generate a cache key for a specific type.
     */
    public function rememberCache(callable $callback, string $type, array $data = []): mixed
    {
        $cacheKey = $this->generateTypeCacheKey($type, $data);
        $ttl = $this->getCacheTtl($type);

        return ModularityCache::remember($cacheKey, $ttl, $callback, $this->getCacheModuleName(), $this->getCacheModuleRouteName());
    }
}
