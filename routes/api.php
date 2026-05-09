<?php

use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Http\Controllers\Utility\SlugInputGenerateController;
use Unusualify\Modularous\Http\Controllers\Utility\SlugInputValidationController;

/*
|--------------------------------------------------------------------------
| Api Routes
|
| Middlewares : [ 'web.auth', ...\Unusualify\Modularous\Facades\ModularousRoutes::defaultMiddlewares(), \Unusualify\Modularous\Facades\ModularousRoutes::defaultPanelMiddlewares()]
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('inputs/slug/validate', SlugInputValidationController::class)
    ->name('inputs.slug.validate');

Route::post('inputs/slug/generate', SlugInputGenerateController::class)
    ->name('inputs.slug.generate');

// Route::group(['as' => 'api.', 'namespace' => 'API'], function(){
//     Route::apiResource('languages', LanguageController::class, ['only' => 'index']);
// });

// Route::group([ 'prefix' => 'filepond'], function(){
//     Route::middleware(['web'])->withoutMiddleware(['web.auth', \Unusualify\Modularous\Facades\ModularousRoutes::defaultPanelMiddlewares(), ...\Unusualify\Modularous\Facades\ModularousRoutes::defaultMiddlewares()])->group(function(){
//         Route::post('process', ['as' => 'filepond.process', 'uses' => 'FilepondController@upload']);
//         Route::delete('revert', ['as' => 'filepond.revert', 'uses' => 'FilepondController@revert']);
//         Route::get('preview/{folder}', ['as' => 'filepond.preview', 'uses' => 'FilepondController@preview']);
//     });
// });
