<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Http\Controllers\Front\CmsController;
use Modules\Cms\Services\CmsPublicModelResolver;
use Modules\Cms\Services\CmsSignedPreviewTargetResolver;
use Unusualify\Modularity\Http\Controllers\Controller;

/**
 * Public signed URL: resolves {@code module} + {@code route} + {@code id} to the correct {@see CmsController} presentation
 * without publication scopes (any {@code HasParentSegment} submodule with a front controller).
 */
final class CmsSignedPublicPreviewController extends Controller
{
    public function __invoke(
        Request $request,
        CanonicalUrlResolverInterface $canonical,
        CmsSignedPreviewTargetResolver $targetResolver,
        CmsPublicModelResolver $publicModelResolver,
    ) {
        $moduleSeg = (string) $request->route('module');
        $routeSeg = (string) $request->route('route');
        $id = $request->route('id');
        $localeRaw = $request->route('locale');
        $locale = $localeRaw !== null && $localeRaw !== ''
            ? (string) $localeRaw
            : (string) modularityConfig('cms_routing.default_locale', config('app.locale'));

        $target = $targetResolver->resolve($moduleSeg, $routeSeg);
        if ($target === null) {
            abort(404);
        }

        $repository = $target['module']->getRepository($target['routeKey'], true);
        $modelClass = get_class($repository->getModel());

        $item = $publicModelResolver->resolveByIdBypassingPublicationScopes($modelClass, $id, $locale);
        if ($item === null) {
            abort(404);
        }

        /** @var CmsController $front */
        $front = app($target['frontControllerFqcn']);

        return $front->renderSignedPublicPreview($request, $canonical, $item);
    }
}
