<?php

use Illuminate\Support\Facades\Route;

Route::prefix(curtModuleUrlPrefix(__FILE__))->name(curtModuleRouteNamePrefix(__FILE__) . '.')->group(function () {
    // $register = modularityConfig('cms_features.register_middlewares', true);

    // $useCanonicalLocaleMiddleware = $register
    //     && modularityConfig('cms_routing.redirect_to_canonical', false);

    // $useVisitorRedirect = $register
    //     && modularityConfig('cms_routing.visitor_redirects_enabled', true);

    // $middlewares = array_values(array_filter([
    //     'web',
    //     $useCanonicalLocaleMiddleware ? 'modules.cms.canonical.locale' : null,
    //     $useVisitorRedirect ? 'modules.cms.visitor.redirect' : null,
    // ]));

    // Route::middleware($middlewares)->group(function () {
    //     if (modularityConfig('cms_routing.public_pages_enabled', true)) {
    //         Route::get('/{path?}', PublicPageController::class)
    //             ->where('path', '.*')
    //             ->name('page');
    //     }
    // });
});
