<?php

namespace Unusualify\Modularous\Tests\Repositories;

use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\FilepondsTrait;
use Unusualify\Modularous\Repositories\Traits\FilesTrait;
use Unusualify\Modularous\Repositories\Traits\ImagesTrait;
use Unusualify\Modularous\Repositories\Traits\PricesTrait;

class TestRepository extends Repository
{
    use PricesTrait, FilesTrait, ImagesTrait, FilepondsTrait;

    public function __construct(TestModel $model)
    {
        $this->model = $model;
    }
}
