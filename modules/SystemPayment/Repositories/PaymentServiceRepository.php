<?php

namespace Modules\SystemPayment\Repositories;

use Modules\SystemPayment\Entities\PaymentService;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\ImagesTrait;
use Unusualify\Modularous\Repositories\Traits\SpreadableTrait;

class PaymentServiceRepository extends Repository
{
    use ImagesTrait, SpreadableTrait;

    public function __construct(PaymentService $model)
    {
        $this->model = $model;
    }
}
