<?php

declare(strict_types=1);

/**
 * Auth component (ue-auth) layout and styling configuration (deferred).
 *
 * Override in modularity/auth_component.php to customize Auth.vue layout.
 * Passed to Vue via layout STORE; Auth.vue reads from window.MODULARITY.AUTH_COMPONENT.
 *
 * useLegacy: When true, layout uses UeCustomAuth (from resources/vendor/modularity/js/components/Auth.vue).
 *
 * @see resources/views/auth/layout.blade.php
 * @see vue/src/js/components/Auth.vue
 */
return [
    'useLegacy' => false,

    'formWidth' => [
        'xs' => '85vw',
        'sm' => '450px',
        'md' => '450px',
        'lg' => '500px',
        'xl' => '600px',
        'xxl' => 700,
    ],

    'layout' => [
        'leftColumnClass' => 'py-12 d-flex flex-column align-center justify-center bg-white',
        'rightColumnClass' => 'px-xs-12 py-xs-3 px-sm-12 py-sm-3 pa-12 pa-md-0 d-flex flex-column align-center justify-center col-right bg-primary',
        'bannerMaxWidth' => '420px',
    ],

    'banner' => [
        'titleClass' => 'text-white mt-5 text-h4 custom-mb-8rem fs-2rem',
        'buttonVariant' => 'outlined',
        'buttonClass' => 'text-white custom-right-auth-button my-5',
    ],

    'dividerText' => 'or',
];
