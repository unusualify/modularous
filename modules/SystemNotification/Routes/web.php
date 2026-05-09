<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemNotification\Http\Controllers\MyNotificationController;
use Unusualify\Modularous\Facades\ModularousRoutes;

/*
|--------------------------------------------------------------------------
| Panel Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group, prefix, and route name alias.
|
| Now create something great!
|
*/
Route::middleware(['web.auth', ...ModularousRoutes::defaultMiddlewares()])->group(function () {

    Route::middleware((ModularousRoutes::defaultPanelMiddlewares()))->group(function () {
        Route::prefix('notifications')->as('my_notification.')->group(function () {
            Route::get('bulk-mark-read', [MyNotificationController::class, 'markReadMyNotifications'])->name('bulkMarkRead');
        });
    });

});
