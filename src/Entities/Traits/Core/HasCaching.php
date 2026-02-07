<?php

namespace Unusualify\Modularity\Entities\Traits\Core;

use Unusualify\Modularity\Entities\Observers\CacheObserver;
use Unusualify\Modularity\Traits\Cache\Cacheable;

/**
 * Trait HasCaching
 *
 * Add this trait to models that should have automatic cache invalidation.
 * The cache will be invalidated when the model is created, updated, deleted,
 * restored, or force deleted.
 */
trait HasCaching
{
    use Cacheable;

    /**
     * Boot the HasCaching trait.
     */
    public static function bootHasCaching(): void
    {
        static::observe(CacheObserver::class);
    }

    /**
     * Determine if cache invalidation should be performed for this model.
     * Override this method to conditionally disable cache invalidation.
     */
    public function shouldCacheInvalidate(): bool
    {
        return true;
    }

    /**
     * Temporarily disable cache invalidation for the current instance.
     */
    public function withoutCacheInvalidation(): static
    {
        $this->skipCacheInvalidation = true;

        return $this;
    }

    /**
     * Re-enable cache invalidation for the current instance.
     */
    public function withCacheInvalidation(): static
    {
        $this->skipCacheInvalidation = false;

        return $this;
    }

    protected function newInstanceHasCaching($instance, $attributes, $exists): static
    {
        $instance->dependentWarmingEnabled = $this->dependentWarmingEnabled ?? true;

        return $instance;
    }
}
