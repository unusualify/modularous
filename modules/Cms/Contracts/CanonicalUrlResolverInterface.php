<?php

namespace Modules\Cms\Contracts;

interface CanonicalUrlResolverInterface
{
    public function resolve(?string $host, string $path, ?string $locale = null, array $options = []): array;

    public function normalizePath(string $path): string;

    /**
     * Values to match against {@see \Modules\Cms\Entities\UrlRoute::normalized_path} (legacy rows may omit a leading slash).
     *
     * @return list<string>
     */
    public function normalizedPathRegistryLookupVariants(string $pathKey): array;
}
