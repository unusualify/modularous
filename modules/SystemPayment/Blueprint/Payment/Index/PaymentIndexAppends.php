<?php

namespace Modules\SystemPayment\Blueprint\Payment\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;

final class PaymentIndexAppends implements ModuleRouteBlueprintProvider
{
    /**
     * @return list<string>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'is_cancelable',
            'is_refundable',
        ];
    }
}
