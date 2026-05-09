<?php

namespace Modules\SystemPricing\Repositories;

use Modules\SystemPricing\Entities\PriceType;
use Unusualify\Modularous\Repositories\Repository;

class PriceTypeRepository extends Repository
{
    public function __construct(PriceType $model)
    {
        $this->model = $model;
    }
}
