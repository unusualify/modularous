<?php

namespace Modules\Cms\Repositories;

use Modules\Cms\Entities\Redirect;
use Modules\Cms\Repositories\Traits\CmsRedirectUrlRouteRegistryTrait;
use Unusualify\Modularous\Repositories\Repository;

class RedirectRepository extends Repository
{
    use CmsRedirectUrlRouteRegistryTrait;

    public function __construct(Redirect $model)
    {
        $this->model = $model;
    }
}
