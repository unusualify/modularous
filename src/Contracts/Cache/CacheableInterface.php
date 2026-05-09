<?php

namespace Unusualify\Modularous\Contracts\Cache;

use Unusualify\Modularous\Contracts\ModuleableInterface;

interface CacheableInterface extends ModuleableInterface
{
    public function shouldUseCache(?string $type = null): bool;

    public function withCache(bool $enabled = true): static;

    public function withoutCache(): static;

    public function getCacheModuleName();

    public function getCacheModuleRouteName();
}
