<?php

namespace Modules\Cms\Localization;

use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Contracts\CmsLocalizationOverrideProviderInterface;

/**
 * Applies optional {@see CmsLocalizationOverrideProviderInterface} (DB / SiteSetting) on top of a concrete driver.
 */
final class DelegatingCmsLocalizationAdapter implements CmsLocalizationContract
{
    public function __construct(
        private CmsLocalizationContract $inner,
        private CmsLocalizationOverrideProviderInterface $overrides,
    ) {}

    public function driver(): string
    {
        return $this->inner->driver();
    }

    public function pathSegmentLocales(): array
    {
        return $this->overrides->pathSegmentLocales() ?? $this->inner->pathSegmentLocales();
    }

    public function supportedLocalesMeta(): array
    {
        return $this->overrides->supportedLocalesMeta() ?? $this->inner->supportedLocalesMeta();
    }

    public function defaultLocale(): string
    {
        return $this->overrides->defaultLocale() ?? $this->inner->defaultLocale();
    }

    public function hideDefaultLocaleInUrl(): bool
    {
        return $this->overrides->hideDefaultLocaleInUrl() ?? $this->inner->hideDefaultLocaleInUrl();
    }

    public function localizeUrl(?string $url = null, ?string $locale = null): string
    {
        $resolvedLocale = $locale ?? $this->defaultLocale();

        return $this->inner->localizeUrl($url, $resolvedLocale);
    }

    public function stripLocaleFromUrl(?string $url = null): string
    {
        return $this->inner->stripLocaleFromUrl($url);
    }

    public function applyLocaleToApplication(string $locale): void
    {
        $this->inner->applyLocaleToApplication($locale);
    }
}
