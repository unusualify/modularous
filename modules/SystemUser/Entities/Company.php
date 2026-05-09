<?php

namespace Modules\SystemUser\Entities;

use Unusualify\Modularous\Entities\Company as ModularousCompany;

class Company extends ModularousCompany
{
    /**
     * The class that is used to spread the company.
     *
     * @var string
     */
    public static $spreadableClass = ModularousCompany::class;
}
