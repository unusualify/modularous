<?php

namespace Unusualify\Modularity\Traits\Cache;

use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Traits\ManageTraits;

trait HasUserAwareCache
{
    use ManageTraits;

    protected ?bool $userAwareCache = null;

    public function shouldUseUserAwareCache(): bool
    {
        // Explicit setting takes precedence
        if ($this->userAwareCache !== null) {
            return $this->userAwareCache;
        }

        // Otherwise, detect from traits
        return $this->hasUserAwareCache();
    }

    public function withUserAwareCache(bool $enabled = true): static
    {
        $this->userAwareCache = $enabled;

        return $this;
    }

    public function withSharedCache(): static
    {
        return $this->withUserAwareCache(false);
    }

    public function getUserCacheIdentifier(): string
    {
        $user = Auth::user();

        if (! $user) {
            return 'guest';
        }

        return 'u' . $user->getAuthIdentifier();
    }

    public function addUserContext(array $params): array
    {
        if ($this->shouldUseUserAwareCache()) {
            $params['_user'] = $this->getUserCacheIdentifier();
        }

        return $params;
    }

    protected function hasUserAwareCache(): bool
    {
        $hasUserAwareCache = false;

        foreach ($this->traitProperties(__FUNCTION__) as $property) {
            if ((bool) $this->$property) {
                $hasUserAwareCache = true;

                break;
            }
        }

        return $hasUserAwareCache;
    }
}
