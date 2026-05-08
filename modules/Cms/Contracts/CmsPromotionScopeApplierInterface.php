<?php

namespace Modules\Cms\Contracts;

/**
 * Runs post-promotion work for enabled scope flags (cache is handled in {@see \Modules\Cms\Services\CmsPromotionService}).
 * Swap or extend the binding to plug in search reindex, webhooks, or cross-service sync.
 */
interface CmsPromotionScopeApplierInterface
{
    /**
     * @param array<string, mixed> $scope Resolved scope flags
     * @param array<string, mixed> $context `dry_run`, `user`, `diff` snapshot
     * @return array{applied: list<string>, skipped: list<string>}
     */
    public function applyAfterPromotion(array $scope, array $context): array;
}
