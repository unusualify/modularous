<?php

return [
    'enabled' => env('MODULAROUS_CMS_PROMOTION_ENABLED', false),

    'dry_run_required' => env('MODULAROUS_CMS_PROMOTION_DRY_RUN_REQUIRED', true),

    'scope' => [
        'settings' => env('MODULAROUS_CMS_PROMOTION_SCOPE_SETTINGS', true),
        'content' => env('MODULAROUS_CMS_PROMOTION_SCOPE_CONTENT', true),
        'seo' => env('MODULAROUS_CMS_PROMOTION_SCOPE_SEO', true),
        'redirects' => env('MODULAROUS_CMS_PROMOTION_SCOPE_REDIRECTS', true),
        'layouts' => env('MODULAROUS_CMS_PROMOTION_SCOPE_LAYOUTS', true),
    ],

    'approval' => [
        'enabled' => env('MODULAROUS_CMS_PROMOTION_APPROVAL_ENABLED', true),
        'roles' => array_filter(array_map('trim', explode(',', env('MODULAROUS_CMS_PROMOTION_APPROVER_ROLES', 'superadmin,admin')))),
        'emails' => array_filter(array_map('trim', explode(',', env('MODULAROUS_CMS_PROMOTION_APPROVER_EMAILS', '')))),
        'checkpoint_label' => env('MODULAROUS_CMS_PROMOTION_CHECKPOINT_LABEL', 'cms-promotion-approval'),
    ],

    /**
     * Optional second Laravel DB connection (e.g. `staging`, `mysql_prod`) for dry-run **count deltas** vs default.
     * Does not copy data — read-only snapshots. Restrict with `allowed_connections` in production.
     */
    'compare' => [
        'connection' => env('MODULAROUS_CMS_PROMOTION_COMPARE_CONNECTION', ''),
        'label' => env('MODULAROUS_CMS_PROMOTION_COMPARE_LABEL', 'target'),
        /** Non-empty = only these connection names may be used for compare (e.g. ['staging','mysql_uat']) */
        'allowed_connections' => array_values(array_filter(array_map('trim', explode(',', env('MODULAROUS_CMS_PROMOTION_COMPARE_ALLOWED', ''))))),
        /** When true, include full secondary snapshot in API (large JSON). Default: deltas only. */
        'include_full_target_snapshot' => env('MODULAROUS_CMS_PROMOTION_COMPARE_INCLUDE_TARGET', false),
    ],

    'execute' => [
        /** When true, runs Cache::flush() after modularous cache (use with care). */
        'flush_laravel_cache' => env('MODULAROUS_CMS_PROMOTION_FLUSH_LARAVEL_CACHE', false),
    ],

    'audit' => [
        'activity_log' => env('MODULAROUS_CMS_PROMOTION_AUDIT_ACTIVITY', true),
        'log_channel' => env('MODULAROUS_CMS_PROMOTION_AUDIT_LOG_CHANNEL', 'modularous'),
    ],
];
