<?php

return [
    // Infrastructure registration toggles for CMS facilities.
    // Feature behavior remains controlled by security/cms_promotion/cms_seo/cms_routing.
    'enabled' => env('MODULARITY_CMS_FEATURES_ENABLED', true),
    'register_contracts' => env('MODULARITY_CMS_REGISTER_CONTRACTS', true),
    'register_middlewares' => env('MODULARITY_CMS_REGISTER_MIDDLEWARES', true),
];
