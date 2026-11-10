<?php

namespace Modules\SystemNotification\Blueprint\Notification\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

final class NotificationIndexWith implements ModuleRouteBlueprintProvider
{
    /**
     * @return array<string, mixed>|list<string>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'recipient',
        ];
    }
}
