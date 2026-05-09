<?php

namespace Modules\SystemPayment\Repositories;

use Modules\SystemPayment\Entities\PaymentCurrency;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\RepeatersTrait;
use Unusualify\Modularous\Repositories\Traits\SpreadableTrait;

class PaymentCurrencyRepository extends Repository
{
    use SpreadableTrait, RepeatersTrait;

    public function __construct(PaymentCurrency $model)
    {
        $this->model = $model;
    }
}
