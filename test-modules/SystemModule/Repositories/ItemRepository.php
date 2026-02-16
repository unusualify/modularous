<?php

namespace TestModules\SystemModule\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use TestModules\SystemModule\Entities\Item;

class ItemRepository extends Repository
{
    public function __construct(Item $model)
    {
        $this->model = $model;
    }
}
