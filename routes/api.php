<?php

use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Http\Controllers\Utility\SlugInputValidationController;

/*
|--------------------------------------------------------------------------
| Api Routes
|
| Middlewares : [ 'web.auth', ...\Unusualify\Modularity\Facades\ModularityRoutes::defaultMiddlewares(), \Unusualify\Modularity\Facades\ModularityRoutes::defaultPanelMiddlewares()]
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('inputs/slug/validate', SlugInputValidationController::class)
    ->name('inputs.slug.validate');

// Route::group(['as' => 'api.', 'namespace' => 'API'], function(){
//     Route::apiResource('languages', LanguageController::class, ['only' => 'index']);
// });

// Route::group([ 'prefix' => 'filepond'], function(){
//     Route::middleware(['web'])->withoutMiddleware(['web.auth', \Unusualify\Modularity\Facades\ModularityRoutes::defaultPanelMiddlewares(), ...\Unusualify\Modularity\Facades\ModularityRoutes::defaultMiddlewares()])->group(function(){
//         Route::post('process', ['as' => 'filepond.process', 'uses' => 'FilepondController@upload']);
//         Route::delete('revert', ['as' => 'filepond.revert', 'uses' => 'FilepondController@revert']);
//         Route::get('preview/{folder}', ['as' => 'filepond.preview', 'uses' => 'FilepondController@preview']);
//     });
// });
