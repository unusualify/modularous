<?php

declare(strict_types=1);
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\AuthFormBuilder;

/**
 * Auth pages configuration (deferred - loaded when translator is ready).
 *
 * Override in modularous/auth_pages.php. All auth component attributes
 * (bannerDescription, bannerSubDescription, redirectUrl, etc.) are custom
 * and come from app config, not this package.
 *
 * App config structure (in modularous/auth_pages.php):
 *   attributes       - global attributes for all auth pages
 *   pages.[key].attributes - per-page overrides
 *
 * @see AuthFormBuilder
 */
return [
    'component_name' => 'ue-auth',
    /*
    |--------------------------------------------------------------------------
    | Default layout attributes (ue-auth component)
    |--------------------------------------------------------------------------
    */
    'layout' => [
        'logoSymbol' => 'main-logo-dark',
        'logoLightSymbol' => 'main-logo-light',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auth page definitions
    |--------------------------------------------------------------------------
    | Each key maps to a controller method. Override formDraft, actionRoute,
    | buttonText, layoutPreset, formSlotsPreset, slotsPreset to customize.
    | Use 'attributes' to pass page-specific props to the auth component (ue-auth or ue-custom-auth).
    */
    'pages' => [
        'login' => [
            'pageTitle' => 'authentication.login',
            'layoutPreset' => 'banner',
            'formDraft' => 'login_form',
            'actionRoute' => 'admin.login',
            'formTitle' => 'authentication.login-title',
            'buttonText' => 'authentication.sign-in',
            'formSlotsPreset' => 'login_options',
            'slotsPreset' => 'login_bottom',
        ],
        'login_mfa' => [
            'pageTitle' => 'authentication.login',
            'layoutPreset' => 'minimal',
            'formDraft' => 'login_email_form',
            'actionRoute' => 'admin.login',
            'formTitle' => 'authentication.login-title',
            'buttonText' => 'authentication.sign-in',
            'formSlotsPreset' => 'login_mfa_options',
            'slotsPreset' => 'login_mfa_bottom',
        ],
        'login_2fa' => [
            'pageTitle' => 'authentication.verify-login',
            'layoutPreset' => 'minimal',
            'formDraft' => 'login_2fa_form',
            'actionRoute' => 'admin.login-2fa',
            'formTitle' => 'authentication.verify-login',
            'buttonText' => 'authentication.login',
            'formSlotsPreset' => 'login_2fa_options',
            'formOverrides' => ['noValidation' => true],
            'slotsPreset' => null,
        ],
        'step_up' => [
            'pageTitle' => 'authentication.verify-login',
            'layoutPreset' => 'minimal',
            'formDraft' => 'step_up_form',
            'actionRoute' => 'admin.step-up.verify',
            'formTitle' => 'authentication.verify-login',
            'buttonText' => 'authentication.login',
            'formSlotsPreset' => 'step_up_options',
            'formOverrides' => ['noValidation' => true, 'async' => false],
            'slotsPreset' => null,
        ],
        'register' => [
            'pageTitle' => 'authentication.register',
            'layoutPreset' => 'banner',
            'formDraft' => 'register_form',
            'actionRoute' => 'admin.register',
            'formTitle' => 'authentication.create-an-account',
            'buttonText' => 'authentication.register',
            'formSlotsPreset' => 'have_account',
            'slotsPreset' => 'register_bottom',
        ],
        'pre_register' => [
            'pageTitle' => 'authentication.register',
            'layoutPreset' => 'banner',
            'formDraft' => 'pre_register_form',
            'actionRoute' => 'admin.register.verification',
            'formTitle' => 'authentication.create-an-account',
            'buttonText' => 'authentication.register',
            'formSlotsPreset' => 'have_account',
            'slotsPreset' => 'register_bottom',
        ],
        'complete_register' => [
            'pageTitle' => 'authentication.complete-registration',
            'layoutPreset' => 'minimal',
            'formDraft' => 'complete_register_form',
            'actionRoute' => 'admin.complete.register',
            'formTitle' => 'authentication.complete-registration',
            'buttonText' => 'Complete',
            'formSlotsPreset' => 'restart',
            'slotsPreset' => null,
        ],
        'forgot_password' => [
            'pageTitle' => 'authentication.forgot-password',
            'layoutPreset' => 'minimal',
            'formDraft' => 'forgot_password_form',
            'actionRoute' => 'admin.password.reset.email',
            'formTitle' => 'authentication.forgot-password',
            'buttonText' => 'authentication.reset-send',
            'formOverrides' => ['hasSubmit' => false],
            'formSlotsPreset' => 'forgot_password_form',
            'slotsPreset' => 'forgot_password_bottom',
        ],
        'reset_password' => [
            'pageTitle' => 'authentication.reset-password',
            'layoutPreset' => 'minimal',
            'formDraft' => 'reset_password_form',
            'actionRoute' => 'admin.password.reset.update',
            'formTitle' => 'authentication.reset-password',
            'buttonText' => 'authentication.reset-password',
            'formOverrides' => ['hasSubmit' => true, 'color' => 'primary', 'formClass' => 'px-5'],
            'formSlotsPreset' => 'resend',
            'slotsPreset' => null,
        ],
        'oauth_password' => [
            'pageTitle' => 'authentication.confirm-provider',
            'layoutPreset' => 'minimal_no_divider',
            'formDraft' => null,
            'actionRoute' => 'admin.login.oauth.linkProvider',
            'formTitle' => 'authentication.confirm-provider',
            'buttonText' => 'authentication.sign-in',
            'formSlotsPreset' => 'oauth_submit',
            'slotsPreset' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Layout presets (structural flags only)
    |--------------------------------------------------------------------------
    | Content attributes (bannerDescription, bannerSubDescription, redirectUrl, etc.)
    | come from app config: modularous/auth_pages.php attributes and pages.[page].attributes
    */
    'layoutPresets' => [
        'banner' => [
            'noSecondSection' => false,
        ],
        'minimal' => [
            'noSecondSection' => true,
        ],
        'minimal_no_divider' => [
            'noSecondSection' => false,
            'noDivider' => true,
        ],
    ],
];
