<?php

namespace Modules\Cms\Contracts;

/**
 * Optional overrides for {@see CmsLocalizationContract} (future: SiteSetting / DB-backed config).
 * Returning {@code null} from a method means "use the inner adapter's value".
 */
interface CmsLocalizationOverrideProviderInterface
{
    /**
     * @return list<string>|null
     */
    public function pathSegmentLocales(): ?array;

    public function defaultLocale(): ?string;

    public function hideDefaultLocaleInUrl(): ?bool;

    /**
     * @return array<string, array<string, mixed>>|null
     */
    public function supportedLocalesMeta(): ?array;
}
