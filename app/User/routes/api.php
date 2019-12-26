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

Route::group(['prefix' => 'v1', 'middleware' => ['api']], function() {
  Route::post('/auth/login','UserController@login')->name('login');
  Route::group(['prefix' => 'auth', 'middleware'=>['jwt.verify']], function(){
      Route::get('user','UserController@getUser')->name('auth-user');
  });
});