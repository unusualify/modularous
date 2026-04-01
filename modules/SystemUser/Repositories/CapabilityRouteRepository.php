<?php

namespace Modules\SystemUser\Repositories;

use Modules\SystemUser\Entities\CapabilityRoute;
use Unusualify\Modularity\Repositories\Repository;

class CapabilityRouteRepository extends Repository
{
    public function __construct(CapabilityRoute $model)
    {
        $this->model = $model;
    }
}

