@php($stagingGuard = app(\Unusualify\Modularous\Services\Staging\StagingGuard::class))
@if($stagingGuard->enabled())
    <div role="status" data-staging-banner style="position: fixed; bottom: 12px; left: 50%; transform: translateX(-50%); z-index: 10000; pointer-events: none; padding: 4px 12px; border-radius: 999px; background: #b45309; color: #fff; font: 600 12px/1.6 system-ui, sans-serif; box-shadow: 0 2px 8px rgba(0, 0, 0, .2);">
        Staging{{ $stagingGuard->label() ? ' · ' . $stagingGuard->label() : '' }}: changes are locked
    </div>
@endif
