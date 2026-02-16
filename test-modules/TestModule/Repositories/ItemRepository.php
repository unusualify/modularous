<?php

namespace TestModules\TestModule\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use TestModules\TestModule\Entities\Item;

class ItemRepository extends Repository
{
    public function __construct(Item $model)
    {
        $this->model = $model;
    }
}
