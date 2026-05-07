<?php

namespace Modules\Cms\Support;

use Illuminate\Http\Request;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;

/**
 * Head tags for public CMS pages: canonical URL, robots, title/description from translation.
 */
final class CmsPublicSeo
{
    /**
     * @param object|null $translation e.g. {@see \Modules\Cms\Entities\Translations\PageTranslation}
     * @return array{title: string, description: ?string, canonicalUrl: string, robotsMeta: string}
     */
    public static function build(Request $request, ?object $translation, CanonicalUrlResolverInterface $canonical): array
    {
        $title = (string) (optional($translation)->seo_title
            ?? optional($translation)->title
            ?? 'Page');

        $description = optional($translation)->seo_description;
        $description = $description !== null && $description !== '' ? (string) $description : null;

        $canonicalUrl = self::resolveCanonical($request, $translation, $canonical);

        $robotsMeta = self::robotsDirective(
            isset($translation) ? $translation->robots_index : null,
            isset($translation) ? $translation->robots_follow : null,
        );

        return [
            'title' => $title,
            'description' => $description,
            'canonicalUrl' => $canonicalUrl,
            'robotsMeta' => $robotsMeta,
        ];
    }

    private static function resolveCanonical(Request $request, ?object $translation, CanonicalUrlResolverInterface $canonical): string
    {
        $custom = isset($translation) ? trim((string) ($translation->canonical_url ?? '')) : '';

        if ($custom !== '') {
            if (preg_match('#^https?://#i', $custom)) {
                return $custom;
            }

            return rtrim($request->getSchemeAndHttpHost(), '/') . '/' . ltrim($custom, '/');
        }

        $resolved = $canonical->resolve(
            $request->getHost(),
            $request->getPathInfo() ?: '/',
            app()->getLocale(),
            ['redirect_to_canonical' => false]
        );

        return $resolved['canonical_url'] ?? $request->url();
    }

    /**
     * Laravel validation often omits unchecked bools; treat null as "allow" (index/follow).
     */
    private static function robotsDirective(mixed $index, mixed $follow): string
    {
        $noIndex = $index === false;
        $noFollow = $follow === false;

        $indexPart = $noIndex ? 'noindex' : 'index';
        $followPart = $noFollow ? 'nofollow' : 'follow';

        return $indexPart . ', ' . $followPart;
    }
}
