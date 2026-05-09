<?php

namespace Modules\SystemNotification\Repositories;

use Modules\SystemNotification\Entities\MyNotification;
use Unusualify\Modularous\Repositories\Repository;

class MyNotificationRepository extends Repository
{
    public function __construct(MyNotification $model)
    {
        $this->model = $model;
    }
}
