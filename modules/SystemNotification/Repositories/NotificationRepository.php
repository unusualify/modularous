<?php

namespace Modules\SystemNotification\Repositories;

use Modules\SystemNotification\Entities\Notification;
use Unusualify\Modularous\Repositories\Repository;

class NotificationRepository extends Repository
{
    public function __construct(Notification $model)
    {
        $this->model = $model;
    }
}
