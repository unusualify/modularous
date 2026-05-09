<?php

namespace Modules\SystemPricing\Repositories;

use Modules\SystemPricing\Entities\Price;
use Unusualify\Modularous\Repositories\Repository;

class PriceRepository extends Repository
{
    public function __construct(Price $model)
    {
        $this->model = $model;
    }
}
