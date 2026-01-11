<?php

namespace Unusualify\Modularity\Contracts\Cache;

interface UserAwareCacheInterface
{
    public function shouldUseUserAwareCache(): bool;

    public function withUserAwareCache(bool $enabled = true): static;

    public function withSharedCache(): static;

    public function getUserCacheIdentifier(): string;

    public function addUserContext(array $params): array;
}

