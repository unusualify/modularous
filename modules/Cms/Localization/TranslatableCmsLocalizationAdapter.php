<?php

namespace Modules\Cms\Localization;

use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Support\CmsFrontPath;

/**
 * Fallback driver: {@see getLocales()} / {@code config('translatable.locales')} + {@see modularousConfig('cms_routing.*')}.
 * Does not require mcamara.
 */
final class TranslatableCmsLocalizationAdapter implements CmsLocalizationContract
{
    public function __construct(
        private CanonicalUrlResolverInterface $canonical,
    ) {}

    public function driver(): string
    {
        return 'translatable';
    }

    public function pathSegmentLocales(): array
    {
        $configured = modularousConfig('cms_routing.path_segment_locales');
        if (is_array($configured) && $configured !== []) {
            return $this->sortedLongestFirst(array_values(array_unique(array_filter(array_map('strval', $configured)))));
        }

        return $this->sortedLongestFirst(array_values(array_unique(array_map('strval', getLocales()))));
    }

    public function supportedLocalesMeta(): array
    {
        $meta = [];
        foreach ($this->pathSegmentLocales() as $locale) {
            $meta[$locale] = [
                'name' => $locale,
                'native' => $locale,
                'script' => 'Latn',
                'regional' => '',
            ];
        }

        return $meta;
    }

    public function defaultLocale(): string
    {
        return (string) modularousConfig('cms_routing.default_locale', config('app.locale'));
    }

    public function hideDefaultLocaleInUrl(): bool
    {
        return (bool) modularousConfig('cms_routing.hide_default_locale_segment', false);
    }

    public function localizeUrl(?string $url = null, ?string $locale = null): string
    {
        $locale ??= $this->defaultLocale();
        $path = $this->normalizedRequestPath($url);
        [, $inner] = $this->splitLocaleAndInner($path);

        return url(CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath($locale, $inner, $this->canonical));
    }

    public function stripLocaleFromUrl(?string $url = null): string
    {
        $path = $this->normalizedRequestPath($url);
        [, $inner] = $this->splitLocaleAndInner($path);

        return url($inner === '/' ? '/' : $inner);
    }

    public function applyLocaleToApplication(string $locale): void
    {
        app()->setLocale($locale);
    }

    private function normalizedRequestPath(?string $url): string
    {
        if ($url === null || $url === '') {
            return $this->canonical->normalizePath(request()->path());
        }

        if (! str_contains($url, '://') && str_starts_with($url, '/')) {
            return $this->canonical->normalizePath($url);
        }

        $path = parse_url($url, PHP_URL_PATH);

        return $this->canonical->normalizePath(is_string($path) ? $path : '/');
    }

    /**
     * @return array{0: string, 1: string} [locale, inner normalized path for registry]
     */
    private function splitLocaleAndInner(string $normalizedPath): array
    {
        $locales = $this->pathSegmentLocales();
        $default = $this->defaultLocale();

        foreach ($locales as $loc) {
            $needle = '/' . trim($loc, '/');
            if ($normalizedPath === $needle) {
                return [$loc, '/'];
            }
            if (str_starts_with($normalizedPath, $needle . '/')) {
                $inner = mb_substr($normalizedPath, mb_strlen($needle));

                return [$loc, $inner === '' || $inner === '/'
                    ? '/'
                    : $this->canonical->normalizePath($inner)];
            }
        }

        return [$default, $normalizedPath];
    }

    /**
     * @param list<string> $locales
     * @return list<string>
     */
    private function sortedLongestFirst(array $locales): array
    {
        usort($locales, fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        return $locales;
    }
}
