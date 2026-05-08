<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Cms\Services\CmsSiteSeoSettingsService;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Controllers\BaseController;

/**
 * Inertia shell for CMS site-wide SEO (global robots.txt body stored in site_settings).
 */
class SiteSeoToolController extends BaseController
{
    protected $moduleName = 'Cms';

    protected $routeName = 'Page';

    public function __construct(Application $app, Request $request)
    {
        parent::__construct($app, $request);
    }

    public function __invoke(CmsSiteSeoSettingsService $siteSeo): Response
    {
        $pageTitle = __('Site SEO') . ' - ' . Modularity::pageTitle();
        $headerTitle = __('Site SEO');

        $data = [
            'pageTitle' => $pageTitle,
            'headerTitle' => $headerTitle,
            '_mainConfiguration' => [
                'navigation' => $this->siteSeoNavigationWithBreadcrumbs(),
            ],
        ];

        $this->shareInertiaStoreVariables();

        $prefix = $this->module->panelRouteNamePrefix() . '.';

        return Inertia::render('SiteSeo', [
            'siteSeoEndpoints' => [
                'save' => route($prefix . 'siteSeo.save'),
            ],
            'globalRobotsTxt' => $siteSeo->globalRobotsTxtForEditor(),
            'useSiteSettings' => (bool) modularityConfig('cms_seo.robots.use_site_settings', true),
            'endpoints' => new \stdClass,
            'mainConfiguration' => $this->getInertiaMainConfiguration($data),
            'headLayoutData' => $this->getHeadLayoutData($data),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function siteSeoNavigationWithBreadcrumbs(): array
    {
        $navigation = get_modularity_navigation_config();

        $pageIndexRoute = $this->module->panelRouteNamePrefix() . '.page.index';
        $cmsCrumb = [
            'title' => __('CMS'),
            'disabled' => true,
        ];
        if (Route::has($pageIndexRoute)) {
            $cmsCrumb['href'] = route($pageIndexRoute);
            $cmsCrumb['disabled'] = false;
        }

        $navigation['breadcrumbs'] = [
            $cmsCrumb,
            [
                'title' => __('Site SEO'),
                'disabled' => true,
            ],
        ];

        return $navigation;
    }
}
