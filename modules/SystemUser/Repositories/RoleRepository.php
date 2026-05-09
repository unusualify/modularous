<?php

namespace Modules\SystemUser\Repositories;

use Modules\SystemUser\Entities\Role;
use Unusualify\Modularous\Repositories\Repository;

class RoleRepository extends Repository
{
    // public function __construct(\Spatie\Permission\Models\Role $model)
    public function __construct(Role $model)
    {
        $this->model = $model;
    }
}
