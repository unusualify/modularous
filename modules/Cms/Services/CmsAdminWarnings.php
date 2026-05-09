<?php

namespace Modules\Cms\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Modules\Cms\Entities\UrlRoute;

/**
 * Non-blocking hints after a public-routable model save: URL path segment overlap (registry) and optional SEO gaps when published.
 * Expects the same shape as CMS page models (translations, publish flags, slugs) — driven by the saved instance, not a static type.
 */
final class CmsAdminWarnings
{
    public function __construct(
        private CmsUrlRouteRegistry $urlRouteRegistry,
    ) {}

    /**
     * @return list<string>
     */
    public function gather(Model $model): array
    {
        $warnings = [];

        $warnings = array_merge($warnings, $this->pathOverlapWarnings($model));

        if (modularousConfig('cms_seo.admin.publish_soft_warnings', true) && $model->published) {
            $warnings = array_merge($warnings, $this->publishSeoSoftWarnings($model));
        }

        if (modularousConfig('cms_seo.admin.publish_schedule_warnings', true) && $model->published) {
            $warnings = array_merge($warnings, $this->publishScheduleWarnings($model));
        }

        return array_values(array_unique(array_filter($warnings, static fn ($w) => $w !== null && $w !== '')));
    }

    /**
     * @return list<string>
     */
    protected function pathOverlapWarnings(Model $model): array
    {
        if (! $this->urlRouteRegistry->tableReady()) {
            return [];
        }

        $warnings = [];

        $pathsByLocale = $this->urlRouteRegistry->publicPagePathsByLocale($model);

        foreach ($pathsByLocale as $locale => $path) {
            $warnings = array_merge(
                $warnings,
                $this->urlRouteRegistry->nestedPathPrefixWarnings(
                    (string) $locale,
                    (string) $path,
                    UrlRoute::KIND_PAGE_PUBLIC,
                    $model->getMorphClass(),
                    (int) $model->getKey()
                )
            );
        }

        return $warnings;
    }

    /**
     * @return list<string>
     */
    protected function publishSeoSoftWarnings(Model $model): array
    {
        $warnings = [];

        $model->loadMissing('translations');

        foreach ($model->translations as $translation) {
            if (! $translation->active) {
                continue;
            }

            $locale = (string) $translation->locale;

            if (trim((string) $translation->seo_title) === '') {
                $warnings[] = sprintf('SEO title is empty for locale "%s".', $locale);
            }

            if (trim((string) $translation->seo_description) === '') {
                $warnings[] = sprintf('SEO description is empty for locale "%s".', $locale);
            }
        }

        return $warnings;
    }

    /**
     * @return list<string>
     */
    protected function publishScheduleWarnings(Model $model): array
    {
        $warnings = [];
        $now = Carbon::now();

        if ($model->publish_start_date && $now->lt($model->publish_start_date)) {
            $warnings[] = sprintf(
                'Not publicly visible yet: scheduled publish starts at %s.',
                $model->publish_start_date->toIso8601String()
            );
        }

        if ($model->publish_end_date && $now->gt($model->publish_end_date)) {
            $warnings[] = sprintf(
                'Not publicly visible anymore: scheduled publish ended at %s.',
                $model->publish_end_date->toIso8601String()
            );
        }

        return $warnings;
    }
}
