<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Facades\Modularity;

class SlugHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     * @var array
     */
    public $requirements = [
        'label' => 'Slug',
        'default' => '',
        'localeScoped' => true,
        'excludeId' => null,
        'locale' => null,
        /** When true (default), the slug input exposes an active toggle and submits `{ slug, active }` per locale. */
        'manageActive' => true,
        /**
         * Optional. Mirrored title/source text for one-click slug generation (see {@see \SlugInputGenerateController}).
         * Often populated via form {@code set:} events from the title field.
         */
        'slugSourceValue' => null,
    ];

    /**
     * Manipulate Input Schema Structure
     */
    public function hydrate(): array
    {
        $input = $this->input;

        $input['type'] = 'input-slug';

        if (isset($input['_moduleName']) && isset($input['_routeName'])) {
            $input['endpoint'] = resolve_route(Route::hasAdmin('inputs.slug.validate'));
            if (Route::hasAdmin('inputs.slug.generate')) {
                $input['generateEndpoint'] = resolve_route(Route::hasAdmin('inputs.slug.generate'));
            }
            $input = $this->appendParentSegmentPrefixSchema($input);
        }

        if (modularityConfig('cms_routing.admin.slug_public_path_preview', true)) {
            $input['cmsPublicPathPreview'] = [
                'prefix' => trim((string) modularityConfig('cms_routing.front_route_prefix', 'cms'), '/'),
                'default_locale' => (string) modularityConfig('cms_routing.default_locale', config('app.locale')),
                'hide_default_locale' => (bool) modularityConfig('cms_routing.hide_default_locale_segment', false),
            ];
        }

        $this->addTranslatedProps($input, 'slugSourceValue');

        $input['rules'] ??= 'required';

        return $input;
    }

    /**
     * When the submodule repository uses {@see \Modules\Cms\Repositories\Traits\ParentSegmentTrait}
     * (via {@see \Modules\Cms\Repositories\Traits\CMRTrait}), or the route model uses
     * {@see HasParentSegment} / {@see \Unusualify\Modularity\Entities\Traits\IsCmr}, pass locale → normalized prefix map for the slug field prefix.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    protected function appendParentSegmentPrefixSchema(array $input): array
    {
        if (! class_exists(\Modules\Cms\Services\CmsParentSegmentResolver::class)) {
            return $input;
        }

        $module = Modularity::find($input['_moduleName']);
        if ($module === null) {
            return $input;
        }

        $routeName = $input['_routeName'];
        $modelFqcn = $this->resolveRouteModelFqcn($module, $routeName);
        if ($modelFqcn === null) {
            return $input;
        }

        if (! $this->routeUsesParentSegmentFeatures($module, $routeName, $modelFqcn)) {
            return $input;
        }

        $resolver = App::make(\Modules\Cms\Services\CmsParentSegmentResolver::class);
        if (! $resolver->enabled() || ! $resolver->tablesReady()) {
            return $input;
        }

        $input['parentSegmentPrefixByLocale'] = $resolver->normalizedPrefixesMapForTargetClass($modelFqcn);

        return $input;
    }

    /**
     * @param object $module \Unusualify\Modularity\Module
     */
    protected function resolveRouteModelFqcn($module, string $routeName): ?string
    {
        try {
            $fqcn = $module->getModel($routeName, false);
        } catch (\Throwable) {
            return null;
        }

        return is_string($fqcn) && class_exists($fqcn) ? $fqcn : null;
    }

    /**
     * @param object $module \Unusualify\Modularity\Module
     */
    protected function routeUsesParentSegmentFeatures($module, string $routeName, string $modelFqcn): bool
    {
        try {
            $repoClass = $module->getRepository($routeName, false);
        } catch (\Throwable) {
            $repoClass = null;
        }


        if (is_string($repoClass) && $repoClass !== '' && class_exists($repoClass)) {
            $repo = App::make($repoClass);
            if (method_exists($repo, 'usesParentSegmentForUrl')) {
                return $repo->usesParentSegmentForUrl();
            }
        }

        return classHasTrait($modelFqcn, \Modules\Cms\Entities\Concerns\HasParentSegment::class);
    }
}
