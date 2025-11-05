<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Unusualify\Modularity\Repositories\Repository;

class LaravelTestRepository extends Repository
{
    public function __construct(LaravelTestModel $model)
    {
        $this->model = $model;
    }
}
