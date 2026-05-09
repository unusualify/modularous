<?php

namespace Modules\SystemUser\Repositories;

use Modules\SystemUser\Entities\Company;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\SpreadableTrait;

class CompanyRepository extends Repository
{
    use SpreadableTrait;

    public function __construct(Company $model)
    {
        $this->model = $model;
    }
}
