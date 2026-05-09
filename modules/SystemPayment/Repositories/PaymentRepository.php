<?php

namespace Modules\SystemPayment\Repositories;

use Modules\SystemPayment\Entities\Payment;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\CreatorTrait;
use Unusualify\Modularous\Repositories\Traits\FilepondsTrait;
use Unusualify\Modularous\Repositories\Traits\SpreadableTrait;

class PaymentRepository extends Repository
{
    use FilepondsTrait, SpreadableTrait, CreatorTrait;

    public function __construct(Payment $model)
    {
        $this->model = $model;
    }
}
