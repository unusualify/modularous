<?php

namespace Modules\SystemPayment\Repositories;

use Unusualify\Modularity\Repositories\Repository;
use Modules\SystemPayment\Entities\PaymentCountry;
use Unusualify\Modularity\Repositories\Traits\RepeatersTrait;
use Unusualify\Modularity\Repositories\Traits\SpreadableTrait;
use Unusualify\Modularity\Repositories\Traits\TranslationsTrait;

class PaymentCountryRepository extends Repository
{
    use TranslationsTrait, SpreadableTrait, RepeatersTrait;

    public function __construct(PaymentCountry $model)
    {
        $this->model = $model;
    }
}
