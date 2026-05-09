<?php

namespace Modules\SystemUtility\Repositories;

use Modules\SystemUtility\Entities\State;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\TranslationsTrait;

class StateRepository extends Repository
{
    use TranslationsTrait;

    public function __construct(State $model)
    {
        $this->model = $model;
    }
}
