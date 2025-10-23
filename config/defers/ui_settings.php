<?php

return [
    'auth' => [
        'logoSymbol' => 'main-logo',
    ],
    'sidebar' => [
        'width' => 264,
        'expandOnHover' => true,
        'rail' => false,
        'location' => 'left',
        'persistent' => false,
        'hideIcons' => false,
        'railWidth' => 130,
        'contentDrawer' => [
            'exists' => false,
            'float' => false,
            'rail' => true,
            'permanent' => true,
        ],

        // runs on Main.vue component but not on store/modules/config.js
        'logoSymbol' => 'main-logo-dark',
    ],
    'secondarySidebar' => [
        'exists' => false,
        'location' => 'right',
        'rail' => false,
        'permanent' => true,
        'max-width' => '10em',
    ],
    'dashboard' => [
        'blocks' => [

        ],
    ],
];
