<?php

namespace Modules\SystemUser\Repositories;

use Spatie\Permission\Models\Permission;
use Unusualify\Modularous\Repositories\Repository;

class PermissionRepository extends Repository
{
    public function __construct(Permission $model)
    {
        $this->model = $model;
    }
}
