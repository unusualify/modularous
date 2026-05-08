<?php

return [
    // Core security controls. Off by default.
    'enabled' => env('MODULARITY_SECURITY_ENABLED', false),

    // Managed dynamically from SystemUser/Capability route.
    'capabilities' => [],

    'mfa' => [
        'enabled' => env('MODULARITY_SECURITY_MFA_ENABLED', false),
        'required_roles' => array_filter(array_map('trim', explode(',', env('MODULARITY_SECURITY_MFA_REQUIRED_ROLES', 'admin,marketing-manager,marketing_manager')))),
        'strict' => env('MODULARITY_SECURITY_MFA_STRICT', false),
        'provider' => env('MODULARITY_SECURITY_MFA_PROVIDER', 'email_otp'), // email_otp | google_totp
        'remove_password_login' => (bool) env('MODULARITY_SECURITY_MFA_REMOVE_PASSWORD', true),
        'register_first_time' => (bool) env('MODULARITY_SECURITY_MFA_REGISTER_FIRST_TIME', true),
        'registration_success_route' => env('MODULARITY_SECURITY_MFA_REGISTRATION_SUCCESS_ROUTE', 'admin.register.verification.success'),
        'session_key' => env('MODULARITY_SECURITY_MFA_SESSION_KEY', '2fa:user:id'),
        'flow_session_key' => env('MODULARITY_SECURITY_MFA_FLOW_SESSION_KEY', '2fa:flow:key'),
        'otp_field' => env('MODULARITY_SECURITY_MFA_OTP_FIELD', 'verify-code'),
        'login_page' => env('MODULARITY_SECURITY_MFA_LOGIN_PAGE', 'login_mfa'),
        'challenge_page' => env('MODULARITY_SECURITY_MFA_CHALLENGE_PAGE', 'login_2fa'),
        'challenge_form_route' => env('MODULARITY_SECURITY_MFA_CHALLENGE_FORM_ROUTE', 'admin.login-2fa.form'),
        'throttle' => env('MODULARITY_SECURITY_MFA_THROTTLE', '6,1'),
        'email_otp' => [
            'length' => (int) env('MODULARITY_SECURITY_MFA_EMAIL_OTP_LENGTH', 6),
            'expire_minutes' => (int) env('MODULARITY_SECURITY_MFA_EMAIL_OTP_EXPIRE_MINUTES', 10),
            'max_attempts' => (int) env('MODULARITY_SECURITY_MFA_EMAIL_OTP_MAX_ATTEMPTS', 5),
            'cache_prefix' => env('MODULARITY_SECURITY_MFA_EMAIL_OTP_CACHE_PREFIX', 'mfa:email-otp'),
        ],
    ],

    'throttle' => [
        'login' => env('MODULARITY_SECURITY_THROTTLE_LOGIN', '8,1'),
        'login_2fa' => env('MODULARITY_SECURITY_THROTTLE_LOGIN_2FA', '6,1'),
        'critical_action' => env('MODULARITY_SECURITY_THROTTLE_CRITICAL', '30,1'),
    ],

    'session' => [
        'idle_timeout_minutes' => (int) env('MODULARITY_SECURITY_IDLE_TIMEOUT_MINUTES', 60),
        'step_up_ttl_minutes' => (int) env('MODULARITY_SECURITY_STEP_UP_TTL_MINUTES', 15),
    ],

    'critical_field_permissions' => [
        'robots_index' => 'page_edit',
        'robots_follow' => 'page_edit',
        'canonical_url' => 'page_edit',
        'head_scripts' => 'site_setting_edit',
        'body_scripts' => 'site_setting_edit',
        'redirect_from' => 'redirect_edit',
        'redirect_to' => 'redirect_edit',
        'status_code' => 'redirect_edit',
    ],

    'step_up' => [
        'enabled' => env('MODULARITY_SECURITY_STEP_UP_ENABLED', false),
        // Managed dynamically from SystemUser/Capability route (requires_step_up=true).
        'required_capabilities' => [],
        'provider' => 'email_otp',
        'otp_field' => 'verify-code',
        'page' => 'step_up',
        'challenge_form_route' => 'admin.step-up.form',
        'verify_route' => 'admin.step-up.verify',
        'resend_route' => 'admin.step-up.resend',
        'user_session_key' => 'step-up:user:id',
        'flow_session_key' => 'step-up:flow:key',
        'capability_session_key' => 'step-up:capability:key',
        'pending_request_session_key' => 'step-up:pending:request',
        'return_url_session_key' => 'step-up:return:url',
        'email_otp' => [
            'length' => 6,
            'expire_minutes' => 10,
            'max_attempts' => 5,
            'cache_prefix' => 'step-up:email-otp',
        ],
    ],
];
