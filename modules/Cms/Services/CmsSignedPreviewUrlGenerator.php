<?php

namespace Modules\Cms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

/**
 * Builds time-limited signed URLs for unauthenticated CMS public preview (any HasParentSegment + front stack route).
 */
final class CmsSignedPreviewUrlGenerator
{
    /**
     * @param string $moduleName Module name as in {@see Module::getName()} (normalized to Studly in the URL).
     * @param string $routeKey Submodule route key as in {@see Module::getRouteNames()} (e.g. {@code Page}).
     */
    public function temporaryAbsoluteUrl(string $moduleName, string $routeKey, Model $model, string $locale): string
    {
        $minutes = max(5, (int) modularityConfig('cms_routing.signed_preview.ttl_minutes', 60));
        $expiresAt = now()->addMinutes($minutes);

        return URL::temporarySignedRoute(
            'cms.signed_preview.show',
            $expiresAt,
            [
                'module' => studlyName($moduleName),
                'route' => studlyName($routeKey),
                'id' => $model->getKey(),
                'locale' => $locale,
            ],
            true
        );
    }

    public function ttlMinutes(): int
    {
        return max(5, (int) modularityConfig('cms_routing.signed_preview.ttl_minutes', 60));
    }
}
