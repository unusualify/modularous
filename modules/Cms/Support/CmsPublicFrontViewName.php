<?php

namespace Modules\Cms\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;

/**
 * Picks the Blade for {@see \Modules\Cms\Http\Controllers\Front\CmsPublicFrontController} from config or by matching
 * the resolved model to a CMS submodule (same {@code module::route.custom} as per–route front controllers).
 */
final class CmsPublicFrontViewName
{
    /**
     * @return non-empty-string
     */
    public static function forModel(Model $item): string
    {
        $map = (array) modularousConfig('cms_routing.public_front_views_by_model', []);
        $class = get_class($item);
        if (isset($map[$class]) && is_string($map[$class]) && $map[$class] !== '') {
            return $map[$class];
        }

        // $cms = self::cmsModule();
        $module = method_exists($item, 'isModuleRouteClass') && $item->isModuleRouteClass()
            ? $item->getModule()
            : null;
        if ($module !== null) {
            foreach ($module->getRouteNames() as $routeName) {
                if (! $module->isEnabledRoute($routeName)) {
                    continue;
                }
                try {
                    $m = $module->getModel($routeName, true);
                } catch (\Throwable) {
                    continue;
                }
                if ($m::class === $class) {
                    return Str::snake($module->getName()) . '::' . Str::snake($routeName) . '.custom';
                }
            }
        }

        $fallback = (string) modularousConfig('cms_routing.universal_public_front_fallback_view', 'cms::page.custom');
        if ($fallback === '') {
            return 'cms::page.custom';
        }

        return $fallback;
    }

    private static function cmsModule(): ?Module
    {
        foreach (Modularous::allEnabled() as $module) {
            if ($module instanceof Module && $module->getName() === 'Cms') {
                return $module;
            }
        }

        return null;
    }
}
