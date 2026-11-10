<?php

$list = static fn (string $key, string $default = ''): array => array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env($key, $default))
)));

/**
 * Staging guard. When enabled, every side effect below is locked and has to be
 * unlocked explicitly. List values are comma-separated and accept globs
 * (e.g. press_release*, admin.press-release.*).
 */
return [
    'enabled' => (bool) env('MODULAROUS_STAGING', false),

    /** Shown in the panel banner, e.g. "PR #73". */
    'label' => env('MODULAROUS_STAGING_LABEL'),

    /**
     * Tables that HTTP requests may write to (INSERT, UPDATE, DELETE, TRUNCATE, DDL).
     * Repository writes check the model table; the query guard checks every statement,
     * so related tables (translations, slugs, mediables, ...) must be listed as well.
     *
     * @var list<string>
     */
    'writable_tables' => $list('MODULAROUS_STAGING_WRITABLE_TABLES'),

    /**
     * Panel route names that accept non-GET requests.
     *
     * @var list<string>
     */
    'writable_routes' => $list('MODULAROUS_STAGING_WRITABLE_ROUTES'),

    /**
     * Notification channels that may send (mail, database, broadcast, ...); * for all.
     *
     * @var list<string>
     */
    'notification_channels' => $list('MODULAROUS_STAGING_NOTIFICATIONS'),

    /** Whether the mailer may send messages. */
    'mail' => (bool) env('MODULAROUS_STAGING_MAIL', false),

    /**
     * Job, listener, notification or mailable classes that may run. Allowed jobs run
     * synchronously; everything else pushed to a queue is dropped and logged.
     *
     * @var list<string>
     */
    'queue_jobs' => $list('MODULAROUS_STAGING_QUEUE_JOBS'),

    /**
     * Scheduled tasks (matched against the command or description) that schedule:run may execute.
     *
     * @var list<string>
     */
    'schedule_commands' => $list('MODULAROUS_STAGING_SCHEDULE_COMMANDS'),

    /**
     * Hosts the Laravel HTTP client may call.
     *
     * @var list<string>
     */
    'http_hosts' => $list('MODULAROUS_STAGING_HTTP_HOSTS'),

    /**
     * Artisan commands that may run in addition to defaults.commands.
     *
     * @var list<string>
     */
    'commands' => $list('MODULAROUS_STAGING_COMMANDS'),

    /** Apply the query guard to console commands too (off so deploy commands can migrate). */
    'guard_console_queries' => (bool) env('MODULAROUS_STAGING_GUARD_CONSOLE_QUERIES', false),

    'defaults' => [
        /** Always writable: framework bookkeeping and the activity log of allowed writes. */
        'tables' => [
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
            'activity_log',
        ],

        /** Routes that run without the HTTP and query guards so users can sign in and out. */
        'unguarded_routes' => [
            '*login',
            '*login-2fa',
            '*logout',
            '*step-up.resend',
            '*step-up.verify',
        ],

        /** Commands needed to deploy and inspect a staging app. */
        'commands' => [
            'list',
            'help',
            'about',
            'env',
            'migrate',
            'migrate:status',
            'operations:process',
            'package:discover',
            'vendor:publish',
            'storage:link',
            'key:generate',
            'optimize',
            'optimize:clear',
            'config:*',
            'route:*',
            'view:*',
            'event:*',
            'schedule:run',
            'schedule:work',
            'schedule:list',
            'modularous:flush',
            'modularous:cache:*',
            'modularous:staging:status',
        ],
    ],
];
