<?php

namespace Modules\Cms\Http\Controllers;

use Unusualify\Modularous\Http\Controllers\BaseController;

/**
 * Session-backed JSON for panel (dry-run / commit), aligned with {@see \Modules\Cms\Routes\web} pattern.
 */
class CmsSitemapPanelController extends BaseController
{
    protected $moduleName = 'Cms';

    protected $routeName = 'Sitemap';
}
