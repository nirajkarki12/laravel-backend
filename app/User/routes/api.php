<?php

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
Route::group(['prefix' => 'v1'], function() {
    Route::post('/login','UserController@login')->name('api-login');
    Route::group(['middleware'=>['jwt.auth']],function(){
			Route::get('auth-user','UserController@getUser')->name('auth-user');
    });
});


