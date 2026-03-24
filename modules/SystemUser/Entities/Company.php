<?php

namespace Modules\SystemUser\Entities;

use Unusualify\Modularity\Entities\Company as ModularityCompany;

class Company extends ModularityCompany
{
    /**
     * The class that is used to spread the company.
     *
     * @var string
     */
    public static $spreadableClass = ModularityCompany::class;
}
