<?php

namespace Modules\SystemPayment\Repositories;

use Modules\SystemPayment\Entities\PaymentCountry;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\RepeatersTrait;
use Unusualify\Modularous\Repositories\Traits\SpreadableTrait;
use Unusualify\Modularous\Repositories\Traits\TranslationsTrait;

class PaymentCountryRepository extends Repository
{
    use TranslationsTrait, SpreadableTrait, RepeatersTrait;

    public function __construct(PaymentCountry $model)
    {
        $this->model = $model;
    }
}
