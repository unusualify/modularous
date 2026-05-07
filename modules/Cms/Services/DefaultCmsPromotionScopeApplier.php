<?php

namespace Modules\Cms\Services;

use Modules\Cms\Contracts\CmsPromotionScopeApplierInterface;

/**
 * No-op default: override the binding or subclass to add scope-specific work (search, queues, …).
 * {@see CmsPromotionExecuted} is dispatched from {@see CmsPromotionService} for all listeners.
 */
class DefaultCmsPromotionScopeApplier implements CmsPromotionScopeApplierInterface
{
    public function applyAfterPromotion(array $scope, array $context): array
    {
        return [
            'applied' => [],
            'skipped' => [],
        ];
    }
}
