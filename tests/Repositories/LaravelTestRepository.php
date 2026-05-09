<?php

namespace Unusualify\Modularous\Tests\Repositories;

use Unusualify\Modularous\Repositories\Repository;

class LaravelTestRepository extends Repository
{
    public function __construct(LaravelTestModel $model)
    {
        $this->model = $model;
    }
}
