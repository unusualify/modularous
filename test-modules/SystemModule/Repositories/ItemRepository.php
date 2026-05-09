<?php

namespace TestModules\SystemModule\Repositories;

use TestModules\SystemModule\Entities\Item;
use Unusualify\Modularous\Repositories\Repository;

class ItemRepository extends Repository
{
    public function __construct(Item $model)
    {
        $this->model = $model;
    }
}
