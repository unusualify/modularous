<?php

namespace Unusualify\Modularity\Traits\Cache;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\ModularityCache;

trait WarmupCache
{
    /**
     * Warmup the counts cache for a controller.
     *
     * @param \Unusualify\Modularity\Http\Controllers\BaseController $controller
     * @return bool
     */
    public function warmupControllerCounts($controller)
    {
        $useUserAwareCache = $controller->getRepository()->shouldUseUserAwareCache();

        if ($useUserAwareCache) {
            return;
        }

        $controller->preload();
        $countsList = $controller->getMainCountsList();
        foreach($countsList as $filter) {
            $controller->handleFilterCount($filter, true);
        }

        return true;
    }

    /**
     * Warmup the item cache for a controller.
     *
     * @param \Unusualify\Modularity\Http\Controllers\BaseController $controller
     * @param \Illuminate\Database\Eloquent\Model $item
     * @param bool $cacheFormItem
     * @param bool $cacheFormattedItem
     * @return void
     */
    public function warmupControllerItem($controller, $item, $cacheFormItem, $cacheFormattedItem)
    {
        $controller->preload();

        if( $cacheFormattedItem) {
            $controller->getFormattedIndexItem($item);
        }
        if( $cacheFormItem) {
            $controller->getFormItem($item->id, withoutDefaultScopes: true);
        }
    }

    /**
     * Warmup the items cache for a controller.
     *
     * @param \Unusualify\Modularity\Http\Controllers\BaseController $controller
     * @param int $chunkSize
     * @return void
     */
    public function warmupControllerItems($controller, $chunkSize = 100)
    {
        $controller->preload();
        $repository = $controller->getRepository();

        $cacheFormItem = ModularityCache::isEnabled($controller->getModuleName(), $controller->getRouteName(), 'formItem');
        $cacheFormattedItem = ModularityCache::isEnabled($controller->getModuleName(), $controller->getRouteName(), 'formattedItem');

        $repository->getModel()->each(function ($item, $key) use ($controller, $cacheFormItem, $cacheFormattedItem) {
            $this->warmupControllerItem($controller, $item, $cacheFormItem, $cacheFormattedItem);
        }, $chunkSize);
    }

    /**
     * Warmup the cache for a model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function warmupByModel(Model $model)
    {
        $moduleName = method_exists($model, 'getCacheModuleName') ? $model->getCacheModuleName() : (method_exists($model, 'getModuleName') ? $model->getModuleName() : null);

        if(!$moduleName) {
            return;
        }
        $moduleRouteName = method_exists($model, 'getCacheModuleRouteName') ? $model->getCacheModuleRouteName() : (method_exists($model, 'getRouteName') ? $model->getModuleRouteName() : null);

        $module = Modularity::find($moduleName);

        if(!$module) {
            return;
            throw new \Exception("Module not found: {$moduleName}");
        }

        if(!$module->hasRoute($moduleRouteName)) {
            return;
            throw new \Exception("Route not found: {$moduleRouteName}");
        }

        $controller = $module->getController($moduleRouteName);
        if(!$controller) {
            return;
            throw new \Exception("Controller not found: {$moduleRouteName}");
        }

        if(ModularityCache::isEnabled($moduleName, $moduleRouteName, 'counts')) {
            $this->warmupControllerCounts($controller);
        }

        $cacheFormItem = ModularityCache::isEnabled($moduleName, $moduleRouteName, 'formItem');
        $cacheFormattedItem = ModularityCache::isEnabled($moduleName, $moduleRouteName, 'formattedItem');

        $this->warmupControllerItem($controller, $model, $cacheFormItem, $cacheFormattedItem);
    }

    /**
     * Warmup the counts cache for a module route.
     *
     * @param string $moduleName
     * @param string $routeName
     * @return void
     */
    public function warmupModuleRouteCacheCounts($moduleName, $routeName)
    {
        $module = Modularity::find($moduleName);

        if(!$module) {
            return;
            throw new \Exception("Module not found: {$moduleName}");
        }
        $route = $module->getRoute($routeName);
        if(!$route) {
            return;
            throw new \Exception("Route not found: {$routeName}");
        }
        $controller = $module->getController($routeName);

        if(!$controller) {
            throw new \Exception("Controller not found: {$routeName}");
        }

        if(!ModularityCache::isEnabled($moduleName, $routeName, 'counts')) {
            return;
        }

        $this->warmupControllerCounts($controller);
    }

    /**
     * Warmup the items cache for a module route.
     *
     * @param string $moduleName
     * @param string $routeName
     * @param int $chunkSize
     * @return void
     */
    public function warmupModuleRouteCacheItems($moduleName, $routeName, $chunkSize = 100)
    {
        $module = Modularity::find($moduleName);
        if(!$module) {
            return;
            throw new \Exception("Module not found: {$moduleName}");
        }
        $route = $module->getRoute($routeName);
        if(!$route) {
            return;
            throw new \Exception("Route not found: {$routeName}");
        }
        $controller = $module->getController($routeName);
        if(!$controller) {
            throw new \Exception("Controller not found: {$routeName}");
        }

        $cacheFormItem = ModularityCache::isEnabled($moduleName, $routeName, 'formItem');
        $cacheFormattedItem = ModularityCache::isEnabled($moduleName, $routeName, 'formattedItem');

        if(!$cacheFormItem && !$cacheFormattedItem) {
            return;
        }

        $controller->getModel()->each(function ($item, $key) use ($controller, &$count, $cacheFormItem, $cacheFormattedItem) {
            if( $cacheFormattedItem) {
                $controller->getFormattedIndexItem($item);
            }
            if( $cacheFormItem) {
                $controller->getFormItem($item->id, withoutDefaultScopes: true);
            }
        }, $chunkSize);
    }

    /**
     * Warmup the cache for a module route.
     *
     * @param string $moduleName
     * @param string $routeName
     * @param int $chunkSize
     * @return void
     */
    public function warmupModuleRouteCache($moduleName, $routeName, $chunkSize = 100)
    {
        $this->warmModuleRouteCacheCounts($moduleName, $routeName);
        $this->warmModuleRouteCacheItems($moduleName, $routeName, $chunkSize);
    }
}
