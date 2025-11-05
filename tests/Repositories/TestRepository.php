<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Unusualify\Modularity\Repositories\Repository;

class TestRepository extends Repository
{
    public function __construct(TestModel $model)
    {
        $this->model = $model;
    }
}
