<?php

namespace Modules\SystemSetting\Repositories;

use Modules\SystemSetting\Entities\General;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\ImagesTrait;

class GeneralRepository extends Repository
{
    use ImagesTrait;

    public function __construct(General $model)
    {
        $this->model = $model;
    }
}
