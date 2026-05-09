<?php

namespace Modules\Cms\Http\Controllers\Front;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Services\CmsPublicModelResolver;
use Modules\Cms\Support\CmsPublicFrontViewName;
use Modules\Cms\Support\CmsPublicSeo;

/**
 * Single public catch-all invokable for the CMS module: resolves the entity from {@see \Modules\Cms\Entities\UrlRoute}
 * for any model that is both on the {@see \Modules\Cms\Entities\ParentSegment} registry and uses
 * {@see \Unusualify\Modularous\Entities\Traits\HasParentSegment} — no “first front controller in route order”
 * ambiguity. See {@see \Modules\Cms\Routing\CmsFrontRouteRegistrar::resolveFrontControllerForModule()}.
 */
final class CmsPublicFrontController extends CmsController
{
    /**
     * @var string
     */
    protected $moduleName = 'Cms';

    /**
     * @var string
     */
    protected $routeName = 'Public';

    /**
     * @see CmsController::resolvePublicItem()
     */
    protected function resolvePublicItem(Request $request): ?Model
    {
        $key = $this->publicCmsModuleRouteKey();
        $handler = data_get((array) modularousConfig('cms_routing.public_item_resolvers', []), $key);

        if (is_string($handler) && class_exists($handler)) {
            return app($handler)($request);
        }

        $kind = (string) modularousConfig(
            'cms_routing.public_url_route_kind.' . $key,
            UrlRoute::KIND_PAGE_PUBLIC
        );

        return app(CmsPublicModelResolver::class)->resolveForParentSegmentRegistry($request, $kind);
    }

    /**
     * Renders the same SEO/view payload as {@see CmsController} but the Blade is chosen from the resolved
     * model type (submodule) instead of a fixed per-controller {@code module::route.custom}.
     */
    protected function renderPublicCmsPresentation(
        Request $request,
        Model $item,
        CanonicalUrlResolverInterface $canonical,
        bool $forcePreviewRobotsNoIndex = false
    ) {
        $locale = app()->getLocale();
        $translation = method_exists($item, 'translate') ? $item->translate($locale) : null;

        $seo = CmsPublicSeo::build($request, $translation, $canonical);
        if ($forcePreviewRobotsNoIndex) {
            $seo['robotsMeta'] = 'noindex, nofollow';
        }

        $viewName = CmsPublicFrontViewName::forModel($item);

        return view($viewName, [
            'item' => $item,
            'seoTitle' => $seo['title'],
            'seoDescription' => $seo['description'],
            'canonicalUrl' => $seo['canonicalUrl'],
            'robotsMeta' => $seo['robotsMeta'],
        ]);
    }
}
