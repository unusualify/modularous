<?php

use Illuminate\Support\Facades\Route;

Route::prefix(curtModuleUrlPrefix(__FILE__))->name(curtModuleRouteNamePrefix(__FILE__) . '.')->group(function () {
    // $register = modularousConfig('cms_features.register_middlewares', true);

    // $useCanonicalLocaleMiddleware = $register
    //     && modularousConfig('cms_routing.redirect_to_canonical', false);

    // $useVisitorRedirect = $register
    //     && modularousConfig('cms_routing.visitor_redirects_enabled', true);

    // $middlewares = array_values(array_filter([
    //     'web',
    //     $useCanonicalLocaleMiddleware ? 'modules.cms.canonical.locale' : null,
    //     $useVisitorRedirect ? 'modules.cms.visitor.redirect' : null,
    // ]));

    // Route::middleware($middlewares)->group(function () {
    //     if (modularousConfig('cms_routing.public_pages_enabled', true)) {
    //         Route::get('/{path?}', PublicPageController::class)
    //             ->where('path', '.*')
    //             ->name('page');
    //     }
    // });
});
