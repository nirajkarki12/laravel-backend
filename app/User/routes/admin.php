<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['prefix' => 'admin/v1'], function() {
  Route::post('/auth/login','AdminController@login')->name('login');
  Route::group(['prefix' => 'auth', 'middleware'=>['jwt.verify']], function(){
      Route::get('user','AdminController@getUser')->name('auth-user');
  });
});