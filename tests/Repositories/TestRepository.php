<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\FilepondsTrait;
use Unusualify\Modularity\Repositories\Traits\FilesTrait;
use Unusualify\Modularity\Repositories\Traits\ImagesTrait;
use Unusualify\Modularity\Repositories\Traits\PricesTrait;

class TestRepository extends Repository
{
    use PricesTrait, FilesTrait, ImagesTrait, FilepondsTrait;

    public function __construct(TestModel $model)
    {
        $this->model = $model;
    }
}
