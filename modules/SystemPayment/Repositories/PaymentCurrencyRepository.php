<?php

namespace Modules\SystemPayment\Repositories;

use Modules\SystemPayment\Entities\PaymentCurrency;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\RepeatersTrait;
use Unusualify\Modularity\Repositories\Traits\SpreadableTrait;

class PaymentCurrencyRepository extends Repository
{
    use SpreadableTrait, RepeatersTrait;

    public function __construct(PaymentCurrency $model)
    {
        $this->model = $model;
    }
}
