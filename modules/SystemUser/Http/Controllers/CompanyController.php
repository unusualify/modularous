<?php

namespace Modules\SystemUser\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Unusualify\Modularous\Http\Controllers\BaseController;

class CompanyController extends BaseController
{
    /**
     * @var string
     */
    protected $namespace = 'Modules\SystemUser';

    /**
     * @var string
     */
    protected $moduleName = 'SystemUser';

    /**
     * @var string
     */
    protected $routeName = 'Company';

    protected $titleColumnKey = 'name';

    // protected $perPage = 2;

    /**
     * @var string
     */
    // protected $routePrefix = 'User';

    /**
     * @var string
     */
    protected $modelName = 'Company';

    public function __construct(Application $app, Request $request)
    {
        parent::__construct(
            $app,
            $request
        );
    }
}
