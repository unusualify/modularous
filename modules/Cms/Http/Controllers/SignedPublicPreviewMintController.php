<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cms\Services\CmsPublicModelResolver;
use Modules\Cms\Services\CmsSignedPreviewTargetResolver;
use Modules\Cms\Services\CmsSignedPreviewUrlGenerator;
use Unusualify\Modularous\Http\Controllers\Controller;

/**
 * Panel session GET: mints a time-limited signed CMS public preview URL ({@see cms.signed_preview.show}) for clipboard copy.
 *
 * @see ManageUtilities::signedPublicPreviewFormPayload()
 * @see vue/src/js/hooks/useSignedPublicPreview.js
 */
final class SignedPublicPreviewMintController extends Controller
{
    public function __invoke(
        Request $request,
        CmsSignedPreviewTargetResolver $targetResolver,
        CmsSignedPreviewUrlGenerator $urlGenerator,
        CmsPublicModelResolver $publicModelResolver,
    ): JsonResponse {
        $moduleSeg = (string) $request->route('module');
        $routeSeg = (string) $request->route('route');
        $id = $request->route('id');

        $target = $targetResolver->resolve($moduleSeg, $routeSeg);
        if ($target === null) {
            abort(404);
        }

        $repository = $target['module']->getRepository($target['routeKey'], true);
        $modelClass = get_class($repository->getModel());

        $localeRaw = $request->query('locale');
        $locale = $localeRaw !== null && $localeRaw !== ''
            ? (string) $localeRaw
            : (string) modularousConfig('cms_routing.default_locale', config('app.locale'));

        $item = $publicModelResolver->resolveByIdBypassingPublicationScopes($modelClass, $id, $locale);
        if ($item === null) {
            abort(404);
        }

        $url = $urlGenerator->temporaryAbsoluteUrl(
            $target['module']->getName(),
            $target['routeKey'],
            $item,
            $locale
        );

        return response()->json([
            'url' => $url,
            'expiresInMinutes' => $urlGenerator->ttlMinutes(),
        ]);
    }
}
