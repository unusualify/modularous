<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Cms\Http\Requests\SiteSeoSettingsRequest;
use Modules\Cms\Services\CmsSiteSeoSettingsService;

/**
 * Persists site-wide SEO fields (session web route for the panel).
 */
class SiteSeoSettingsController extends Controller
{
    public function update(SiteSeoSettingsRequest $request, CmsSiteSeoSettingsService $service): JsonResponse
    {
        if (! modularousConfig('cms_seo.robots.use_site_settings', true)) {
            return response()->json([
                'ok' => false,
                'message' => __('Database-backed site SEO is disabled in configuration.'),
            ], 422);
        }

        $data = $request->validated();
        $service->saveGlobalRobotsTxt($data['global_robots_txt'] ?? null);

        return response()->json([
            'ok' => true,
            'message' => __('Site SEO settings saved.'),
        ]);
    }
}
