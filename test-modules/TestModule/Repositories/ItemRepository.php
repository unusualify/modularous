<?php

namespace TestModules\TestModule\Repositories;

use TestModules\TestModule\Entities\Item;
use Unusualify\Modularity\Repositories\Repository;

class ItemRepository extends Repository
{
    public function __construct(Item $model)
    {
        $this->model = $model;
    }
}
