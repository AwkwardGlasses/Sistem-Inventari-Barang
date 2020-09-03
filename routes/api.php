<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//version grouping
// Route::group(['prefix' => 'auth'], function () {
//     //room for authenticated requests
//     Route::group(['middleware' => ['auth:jwt']], function () {

//     });

//     //space for unauthenticated requests
// });

    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', 'AuthController@login');

    // autentikasi dari sini doang
    Route::group(['middleware' => ['auth:jwt']], function () {
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::post('me', 'AuthController@me');
        });
    });
