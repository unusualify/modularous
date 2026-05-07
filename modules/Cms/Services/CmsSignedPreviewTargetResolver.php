<?php

namespace Modules\Cms\Services;

use Illuminate\Support\Collection;
use Modules\Cms\Http\Controllers\Front\CmsController;
use Modules\Cms\Routing\CmsFrontRouteRegistrar;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Module;

/**
 * Resolves panel URL segments ({@code module} + {@code route}) to a CMS public front target (HasParentSegment + {@see CmsController}).
 */
final class CmsSignedPreviewTargetResolver
{
    /**
     * @return array{module: Module, routeKey: string, frontControllerFqcn: class-string<CmsController>}|null
     */
    public function resolve(string $moduleSegment, string $routeSegment): ?array
    {
        $module = Collection::make(Modularity::allEnabled())->first(
            fn ($m) => studlyName($m->getName()) === studlyName($moduleSegment)
        );

        if (! $module instanceof Module) {
            return null;
        }

        $routeKey = Collection::make($module->getRouteNames())->first(
            fn ($r) => studlyName($r) === studlyName($routeSegment)
        );

        if ($routeKey === null || ! $module->isEnabledRoute($routeKey)) {
            return null;
        }

        $frontFqcn = CmsFrontRouteRegistrar::resolveFrontControllerForModuleRoute($module, $routeKey);
        if ($frontFqcn === null) {
            return null;
        }

        return [
            'module' => $module,
            'routeKey' => $routeKey,
            'frontControllerFqcn' => $frontFqcn,
        ];
    }
}
